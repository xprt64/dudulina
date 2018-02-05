<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandDispatcher;

use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandDispatcher;
use Dudulina\Command\CommandMetadata;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\CommandWithMetadata;
use Dudulina\Command\MetadataWrapper as CommandMetadataFactory;
use Dudulina\Command\ValueObject\CommandHandlerAndAggregate;
use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\Event\MetadataFactory as EventMetadataFactory;
use Dudulina\Event\ScheduledEvent;
use Dudulina\FutureEventsStore;
use Dudulina\Scheduling\CommandScheduler;
use Dudulina\Scheduling\ScheduledCommand;
use Gica\Types\Guid;

class DefaultCommandDispatcher implements CommandDispatcher
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
     * @var FutureEventsStore|null
     */
    private $futureEventsStore;
    /**
     * @var EventsApplierOnAggregate
     */
    private $eventsApplierOnAggregate;
    /**
     * @var CommandScheduler|null
     */
    private $commandScheduler;
    /**
     * @var EventMetadataFactory
     */
    private $eventMetadataFactory;
    /**
     * @var CommandMetadataFactory
     */
    private $commandMetadataFactory;

    public function __construct(
        CommandSubscriber $commandSubscriber,
        EventDispatcher $eventDispatcher,
        CommandApplier $commandApplier,
        AggregateRepository $aggregateRepository,
        ConcurrentProofFunctionCaller $functionCaller,
        EventsApplierOnAggregate $eventsApplier,
        EventMetadataFactory $eventMetadataFactory,
        CommandMetadataFactory $commandMetadataFactory,
        ?FutureEventsStore $futureEventsStore = null,
        ?CommandScheduler $commandScheduler = null
    )
    {
        $this->commandSubscriber = $commandSubscriber;
        $this->eventDispatcher = $eventDispatcher;
        $this->commandApplier = $commandApplier;
        $this->aggregateRepository = $aggregateRepository;
        $this->concurrentProofFunctionCaller = $functionCaller;
        $this->futureEventsStore = $futureEventsStore;
        $this->eventsApplierOnAggregate = $eventsApplier;
        $this->commandScheduler = $commandScheduler;
        $this->eventMetadataFactory = $eventMetadataFactory;
        $this->commandMetadataFactory = $commandMetadataFactory;
    }

    public function dispatchCommand(Command $command, CommandMetadata $metadata = null)
    {
        $command = $this->commandMetadataFactory->wrapCommandWithMetadata($command, $metadata);

        /** @var EventWithMetaData[] $eventsWithMetaData */
        /** @var ScheduledCommand[] $scheduledCommands */

        list($eventsWithMetaData, $futureEventsWithMeta, $scheduledCommands, $aggregateClass) = $this->concurrentProofFunctionCaller->executeFunction(function () use ($command) {
            return $this->tryDispatchCommandAndSaveAggregate($command);
        }, $this->getMaximumCommandRetryCount());

        foreach ($eventsWithMetaData as $eventWithMetaData) {
            $this->eventDispatcher->dispatchEvent($eventWithMetaData);
        }

        if ($this->futureEventsStore && !empty($futureEventsWithMeta)) {
            $this->futureEventsStore->scheduleEvents($futureEventsWithMeta);
        }

        if ($this->commandScheduler && !empty($scheduledCommands)) {
            foreach ($scheduledCommands as $scheduledCommand) {
                $this->commandScheduler->scheduleCommand($scheduledCommand, $aggregateClass, $command->getAggregateId(), $metadata);
            }
        }
    }

    private function tryDispatchCommandAndSaveAggregate(CommandWithMetadata $command)
    {
        $handlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);

        list($eventsForNow, $eventsForTheFuture, $scheduledCommands) = $this->applyCommandAndReturnMessages($command, $handlerAndAggregate);

        $eventsForNow = $this->aggregateRepository->saveAggregate($command->getAggregateId(), $handlerAndAggregate->getAggregate(), $eventsForNow);

        return [$eventsForNow, $eventsForTheFuture, $scheduledCommands, $handlerAndAggregate->getCommandHandler()->getHandlerClass()];
    }

    private function loadCommandHandlerAndAggregate(CommandWithMetadata $command): CommandHandlerAndAggregate
    {
        $handler = $this->commandSubscriber->getHandlerForCommand($command->getCommand());

        $aggregate = $this->aggregateRepository->loadAggregate($handler->getHandlerClass(), $command->getAggregateId());

        return new CommandHandlerAndAggregate($handler, $aggregate);
    }

    private function decorateEventWithMetaData($event, MetaData $metaData): EventWithMetaData
    {
        return new EventWithMetaData($event, $metaData->withEventId(Guid::generate()));
    }

    /**
     * @param CommandWithMetadata $command
     * @param CommandHandlerAndAggregate $handlerAndAggregate
     * @return array
     */
    private function applyCommandAndReturnMessages(CommandWithMetadata $command, CommandHandlerAndAggregate $handlerAndAggregate)
    {
        $aggregate = $handlerAndAggregate->getAggregate();
        $handler = $handlerAndAggregate->getCommandHandler();

        $metaData = $this->eventMetadataFactory->factoryEventMetadata($command, $aggregate);

        $newMessageGenerator = $this->commandApplier->applyCommand($aggregate, $command->getCommand(), $handler->getMethodName());

        /** @var EventWithMetaData[] $eventsWithMetaData */
        $eventsWithMetaData = [];

        /** @var EventWithMetaData[] $scheduledEvents */
        $scheduledEvents = [];

        /** @var ScheduledCommand[] $scheduledCommands */
        $scheduledCommands = [];

        foreach ($newMessageGenerator as $message) {
            if ($this->isScheduledCommand($message)) {
                $scheduledCommands[] = $message;
            } else {
                $eventWithMetaData = $this->decorateEventWithMetaData($message, $metaData);
                if (!$this->isScheduledEvent($message)) {
                    $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, [$eventWithMetaData]);
                    $eventsWithMetaData[] = $eventWithMetaData;
                } else {
                    $scheduledEvents[] = $eventWithMetaData;
                }
            }
        }

        return [$eventsWithMetaData, $scheduledEvents, $scheduledCommands];
    }

    private function isScheduledEvent($event): bool
    {
        return $event instanceof ScheduledEvent;
    }

    private function isScheduledCommand($message): bool
    {
        return $message instanceof ScheduledCommand;
    }

    protected function getMaximumCommandRetryCount(): int
    {
        return self::MAXIMUM_SAVE_RETRIES;
    }
}