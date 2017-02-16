<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Command\Exception\CommandValidationFailed;
use Gica\Cqrs\Command\ValueObject\CommandHandlerAndAggregate;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\FutureEvent;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\FutureEventsStore;

class CommandDispatcher
{
    const MAXIMUM_SAVE_RETRIES = 50;

    /**
     * @var CommandSubscriber
     */
    private $commandSubscriber;
    /**
     * @var EventDispatcher
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
        ConcurrentProofFunctionCaller $functionCaller,
        CommandValidator $commandValidator,
        AuthenticatedIdentityReaderService $authService,
        FutureEventsStore $futureEventsStore,
        EventsApplierOnAggregate $eventsApplier
    )
    {
        $this->commandSubscriber = $commandSubscriber;
        $this->eventDispatcher = $eventDispatcher;
        $this->commandApplier = $commandApplier;
        $this->aggregateRepository = $aggregateRepository;
        $this->concurrentProofFunctionCaller = $functionCaller;
        $this->commandValidator = $commandValidator;
        $this->authenticatedIdentityServiceReader = $authService;
        $this->futureEventsStore = $futureEventsStore;
        $this->eventsApplierOnAggregate = $eventsApplier;
    }

    public function dispatchCommand(Command $command)
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            throw new CommandValidationFailed($errors);
        }

        /** @var EventWithMetaData[] $eventsWithMetaData */
        list($eventsWithMetaData, $futureEventsWithMeta) = $this->concurrentProofFunctionCaller->executeFunction(function () use ($command) {
            return $this->tryDispatchCommandAndSaveAggregate($command);
        }, self::MAXIMUM_SAVE_RETRIES);

        foreach ($eventsWithMetaData as $eventWithMetaData) {
            $this->eventDispatcher->dispatchEvent($eventWithMetaData);
        }

        $this->futureEventsStore->scheduleEvents($futureEventsWithMeta);
    }

    private function tryDispatchCommandAndSaveAggregate(Command $command)
    {
        $handlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);

        $eventsWithMetaData = $this->applyCommandAndReturnEvents($command, $handlerAndAggregate);

        list($eventsForNow, $eventsForTheFuture) = $this->splitFutureEvents($eventsWithMetaData);

        $this->aggregateRepository->saveAggregate($command->getAggregateId(), $handlerAndAggregate->getAggregate(), $eventsForNow);

        return [$eventsForNow, $eventsForTheFuture];
    }

    /**
     * @param EventWithMetaData[] $decoratedEvents
     * @return array
     */
    private function splitFutureEvents($decoratedEvents)
    {
        $nowEvents = [];
        $futureEvents = [];

        foreach ($decoratedEvents as $decoratedEvent) {
            if ($this->isFutureEvent($decoratedEvent->getEvent())) {
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
            $errors = $this->commandValidator->validateCommand($command);
            if (!empty($errors)) {
                return false;
            }
            $handlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);
            $this->applyCommandAndReturnEvents($command, $handlerAndAggregate);
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
        return new EventWithMetaData($event, $metaData);
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

        $metaData = $this->factoryMetadata($command, $aggregate);

        $newEventsGenerator = $this->commandApplier->applyCommand($aggregate, $command, $handler->getMethodName());

        /** @var EventWithMetaData[] $eventsWithMetaData */
        $eventsWithMetaData = [];

        foreach ($newEventsGenerator as $event) {
            $eventWithMetaData = $this->decorateEventWithMetaData($event, $metaData);

            if (!$this->isFutureEvent($event)) {
                $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, [$eventWithMetaData]);
            }

            $eventsWithMetaData[] = $eventWithMetaData;
        }

        return $eventsWithMetaData;
    }

    private function isFutureEvent($event): bool
    {
        return $event instanceof FutureEvent;
    }

    private function factoryMetadata(Command $command, $aggregate): MetaData
    {
        return new MetaData(
            $command->getAggregateId(),
            get_class($aggregate),
            new \DateTimeImmutable(),
            $this->authenticatedIdentityServiceReader->getAuthenticatedIdentityId());
    }
}