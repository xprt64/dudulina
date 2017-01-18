<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command;
use Gica\Cqrs\Command\Exception\CommandIsInvalid;
use Gica\Cqrs\Command\ValueObject\CommandHandlerAndAggregate;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\Event\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Event\MetaDataDefault;
use Gica\Cqrs\FutureEventsStore;

class CommandDispatcherDefault implements CommandDispatcher
{
    const MAXIMUM_SAVE_RETRIES = 50;

    /**
     * @var CommandSubscriber
     */
    private $commandSubscriber;
    /**
     * @var EventSubscriber
     */
    private $eventDispatcher;
    /**
     * @var CommandApplier
     */
    private $commandApplier;
    /**
     * @var AggregateRepository
     */
    private $aggregateRepository;
    /**
     * @var ConcurrentProofFunctionCaller
     */
    private $concurrentProofFunctionCaller;
    /**
     * @var CommandValidator
     */
    private $commandValidator;
    /**
     * @var AuthenticatedIdentityReaderService
     */
    private $authenticatedIdentityServiceReader;
    /**
     * @var FutureEventsStore
     */
    private $futureEventsStore;
    /**
     * @var EventsApplierOnAggregate
     */
    private $eventsApplierOnAggregate;

    public function __construct(
        CommandSubscriber $commandSubscriber,
        EventDispatcher $eventDispatcher,
        CommandApplier $commandApplier,
        AggregateRepository $aggregateRepository,
        ConcurrentProofFunctionCaller $concurrentProofFunctionCaller,
        CommandValidator $commandValidator,
        AuthenticatedIdentityReaderService $authenticatedIdentityServiceReader,
        FutureEventsStore $futureEventsStore,
        EventsApplierOnAggregate $eventsApplierOnAggregate
    )
    {
        $this->commandSubscriber = $commandSubscriber;
        $this->eventDispatcher = $eventDispatcher;
        $this->commandApplier = $commandApplier;
        $this->aggregateRepository = $aggregateRepository;
        $this->concurrentProofFunctionCaller = $concurrentProofFunctionCaller;
        $this->commandValidator = $commandValidator;
        $this->authenticatedIdentityServiceReader = $authenticatedIdentityServiceReader;
        $this->futureEventsStore = $futureEventsStore;
        $this->eventsApplierOnAggregate = $eventsApplierOnAggregate;
    }

    public function dispatchCommand(Command $command)
    {
        $errors = $this->commandValidator->validateCommand($command);

        if ($errors) {
            throw new CommandIsInvalid($errors);
        }

        /** @var EventWithMetaData[] $eventsWithMetaData */
        list($eventsWithMetaData, $futureEventsWithMetaData) = $this->concurrentProofFunctionCaller->executeFunction(function () use ($command) {
            return $this->tryDispatchCommandAndSaveAggregate($command);
        }, self::MAXIMUM_SAVE_RETRIES);

        $this->eventDispatcher->dispatchEvents($eventsWithMetaData);

        $this->futureEventsStore->scheduleEvents($futureEventsWithMetaData);
    }

    private function tryDispatchCommandAndSaveAggregate(Command $command)
    {
        $commandHandlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);

        $eventsWithMetaData = $this->applyCommandAndReturnEvents($command, $commandHandlerAndAggregate);

        list($eventsForNowWithMetaData, $eventsForTheFutureWithMetaData) = $this->splitFutureEvents($eventsWithMetaData);

        $this->aggregateRepository->saveAggregate($command->getAggregateId(), $commandHandlerAndAggregate->getAggregate(), $eventsForNowWithMetaData);

        return [$eventsForNowWithMetaData, $eventsForTheFutureWithMetaData];
    }

    /**
     * @param EventWithMetaData[] $decoratedEvents
     * @return array
     */
    public function splitFutureEvents($decoratedEvents)
    {
        $nowEvents = [];
        $futureEvents = [];

        foreach ($decoratedEvents as $decoratedEvent) {
            if ($decoratedEvent->getEvent() instanceof Event\FutureEvent) {
                $futureEvents[] = $decoratedEvent;
            } else {
                $nowEvents[] = $decoratedEvent;
            }
        }

        return [$nowEvents, $futureEvents];
    }

    public function canExecuteCommand(Command $command): bool
    {
        try {
            $commandHandlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);
            $this->applyCommandAndReturnEvents($command, $commandHandlerAndAggregate);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function loadCommandHandlerAndAggregate(Command $command): CommandHandlerAndAggregate
    {
        $handler = $this->commandSubscriber->getHandlerForCommand($command);

        $aggregate = $this->aggregateRepository->loadAggregate($handler->getHandlerClass(), $command->getAggregateId());

        return new CommandHandlerAndAggregate($handler, $aggregate);
    }

    private function decorateEventWithMetaData(Event $event, MetaData $metaData): EventWithMetaData
    {
        return new \Gica\Cqrs\Event\EventWithMetaDataDefault($event, $metaData);
    }

    /**
     * @param Command $command
     * @param CommandHandlerAndAggregate $handlerAndAggregate
     * @return EventWithMetaData[]
     */
    private function applyCommandAndReturnEvents(Command $command, CommandHandlerAndAggregate $handlerAndAggregate)
    {
        $aggregate = $handlerAndAggregate->getAggregate();
        $handler = $handlerAndAggregate->getCommandHandler();

        $metaData = new MetaDataDefault(
            $command->getAggregateId(),
            get_class($aggregate),
            new \DateTimeImmutable(),
            $this->authenticatedIdentityServiceReader->getAuthenticatedIdentityId());

        $newEventsGenerator = $this->commandApplier->applyCommand($aggregate, $command, $handler->getMethodName());

        /** @var EventWithMetaData[] $eventsWithMetaData */
        $eventsWithMetaData = [];

        foreach ($newEventsGenerator as $event) {
            $eventWithMetaData = $this->decorateEventWithMetaData($event, $metaData);

            $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, [$eventWithMetaData]);

            $eventsWithMetaData[] = $eventWithMetaData;
        }

        return $eventsWithMetaData;
    }
}