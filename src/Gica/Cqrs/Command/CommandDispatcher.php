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
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Event\ScheduledEvent;
use Gica\Cqrs\FutureEventsStore;
use Gica\Cqrs\Scheduling\CommandScheduler;
use Gica\Cqrs\Scheduling\ScheduledCommand;
use Gica\Cqrs\Scheduling\ScheduledCommandStore;
use Gica\Cqrs\Scheduling\ScheduledMessage;

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
    /**
     * @var ScheduledCommandStore|null
     */
    private $commandScheduler;

    /**
     * @param CommandSubscriber $commandSubscriber
     * @param EventDispatcher $eventDispatcher
     * @param CommandApplier $commandApplier
     * @param AggregateRepository $aggregateRepository
     * @param ConcurrentProofFunctionCaller $functionCaller
     * @param CommandValidator $commandValidator
     * @param AuthenticatedIdentityReaderService $authService
     * @param FutureEventsStore|null $futureEventsStore
     * @param CommandScheduler|null $commandScheduler
     * @param EventsApplierOnAggregate $eventsApplier
     */
    public function __construct(
        CommandSubscriber $commandSubscriber,
        EventDispatcher $eventDispatcher,
        CommandApplier $commandApplier,
        AggregateRepository $aggregateRepository,
        ConcurrentProofFunctionCaller $functionCaller,
        CommandValidator $commandValidator,
        AuthenticatedIdentityReaderService $authService,
        ?FutureEventsStore $futureEventsStore = null,
        EventsApplierOnAggregate $eventsApplier,
        ?CommandScheduler $commandScheduler = null
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
        $this->commandScheduler = $commandScheduler;
    }

    public function dispatchCommand(Command $command)
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            throw new CommandValidationFailed($errors);
        }

        /** @var EventWithMetaData[] $eventsWithMetaData */
        /** @var ScheduledCommand[] $scheduledCommands */

        list($eventsWithMetaData, $futureEventsWithMeta, $scheduledCommands, $aggregateClass) = $this->concurrentProofFunctionCaller->executeFunction(function () use ($command) {
            return $this->tryDispatchCommandAndSaveAggregate($command);
        }, self::MAXIMUM_SAVE_RETRIES);

        foreach ($eventsWithMetaData as $eventWithMetaData) {
            $this->eventDispatcher->dispatchEvent($eventWithMetaData);
        }

        if ($this->futureEventsStore) {
            $this->futureEventsStore->scheduleEvents($futureEventsWithMeta);
        }

        if ($this->commandScheduler && $scheduledCommands) {
            foreach ($scheduledCommands as $scheduledCommand) {
                $this->commandScheduler->scheduleCommand($scheduledCommand, $aggregateClass, $command->getAggregateId());
            }
        }
    }

    private function tryDispatchCommandAndSaveAggregate(Command $command)
    {
        $handlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);

        $eventsWithMetaData = $this->applyCommandAndReturnEvents($command, $handlerAndAggregate);

        list($eventsForNow, $eventsForTheFuture, $scheduledCommands) = $this->splitMessagesByType($eventsWithMetaData);

        $this->aggregateRepository->saveAggregate($command->getAggregateId(), $handlerAndAggregate->getAggregate(), $eventsForNow);

        return [$eventsForNow, $eventsForTheFuture, $scheduledCommands, $handlerAndAggregate->getCommandHandler()->getHandlerClass()];
    }

    /**
     * @param EventWithMetaData[]|ScheduledCommand[] $messages
     * @return array
     */
    private function splitMessagesByType($messages)
    {
        $nowEvents = [];
        $futureEvents = [];
        $scheduledCommands = [];

        foreach ($messages as $message) {
            if ($this->isScheduledCommand($message)) {
                $scheduledCommands[] = $message;
            } else if ($this->isScheduledEvent($message->getEvent())) {
                $futureEvents[] = $message;
            } else {
                $nowEvents[] = $message;
            }
        }

        return [$nowEvents, $futureEvents, $scheduledCommands];
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
     * @return EventWithMetaData[]|ScheduledCommand[]
     */
    private function applyCommandAndReturnEvents(Command $command, CommandHandlerAndAggregate $handlerAndAggregate)
    {
        $aggregate = $handlerAndAggregate->getAggregate();
        $handler = $handlerAndAggregate->getCommandHandler();

        $metaData = $this->factoryMetadata($command, $aggregate);

        $newMessageGenerator = $this->commandApplier->applyCommand($aggregate, $command, $handler->getMethodName());

        /** @var EventWithMetaData[]|ScheduledCommand[] $eventsWithMetaData */
        $eventsWithMetaData = [];

        foreach ($newMessageGenerator as $message) {
            if ($this->isScheduledCommand($message)) {
                $eventsWithMetaData[] = $message;
            } else {
                $eventWithMetaData = $this->decorateEventWithMetaData($message, $metaData);
                if (!$this->isScheduledMessage($message)) {
                    $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, [$eventWithMetaData]);
                }
                $eventsWithMetaData[] = $eventWithMetaData;
            }
        }

        return $eventsWithMetaData;
    }

    private function isScheduledEvent($event): bool
    {
        return $event instanceof ScheduledEvent;
    }

    private function isScheduledCommand($message): bool
    {
        return $message instanceof ScheduledCommand;
    }

    private function isScheduledMessage($message): bool
    {
        return $message instanceof ScheduledMessage;
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