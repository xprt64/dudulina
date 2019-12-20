<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandDispatcher;

use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandDispatcher;
use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher\SideEffects;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\CommandWithMetadata;
use Dudulina\Command\MetadataWrapper as CommandMetadataFactory;
use Dudulina\Command\ValueObject\CommandHandlerAndAggregate;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\Event\MetadataFactory as EventMetadataFactory;
use Dudulina\Scheduling\ScheduledCommand;
use Gica\Types\Guid;

class DefaultCommandDispatcher implements CommandDispatcher
{
    /**
     * @var CommandSubscriber
     */
    private $commandSubscriber;
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
     * @var EventsApplierOnAggregate
     */
    private $eventsApplierOnAggregate;
    /**
     * @var EventMetadataFactory
     */
    private $eventMetadataFactory;
    /**
     * @var CommandMetadataFactory
     */
    private $commandMetadataFactory;
    /**
     * @var SideEffectsDispatcher
     */
    private $sideEffectsDispatcher;

    public function __construct(
        CommandSubscriber $commandSubscriber,
        CommandApplier $commandApplier,
        AggregateRepository $aggregateRepository,
        ConcurrentProofFunctionCaller $functionCaller,
        EventsApplierOnAggregate $eventsApplier,
        EventMetadataFactory $eventMetadataFactory,
        CommandMetadataFactory $commandMetadataFactory,
        SideEffectsDispatcher $sideEffectsDispatcher
    )
    {
        $this->commandSubscriber = $commandSubscriber;
        $this->commandApplier = $commandApplier;
        $this->aggregateRepository = $aggregateRepository;
        $this->concurrentProofFunctionCaller = $functionCaller;
        $this->eventsApplierOnAggregate = $eventsApplier;
        $this->eventMetadataFactory = $eventMetadataFactory;
        $this->commandMetadataFactory = $commandMetadataFactory;
        $this->sideEffectsDispatcher = $sideEffectsDispatcher;
    }

    public function dispatchCommand(Command $command, $metadata = null)
    {
        $sideEffects = $this->dispatchCommandAndSaveAggregate(
            $this->commandMetadataFactory->wrapCommandWithMetadata($command, $metadata)
        );
        $this->sideEffectsDispatcher->dispatchSideEffects($sideEffects->withCommandMetadata($metadata));
    }

    private function tryDispatchCommandAndSaveAggregate(CommandWithMetadata $command)
    {
        $handlerAndAggregate = $this->loadCommandHandlerAndAggregate($command);
        $dispatchResult = $this->applyCommandAndReturnSideEffects($command, $handlerAndAggregate);
        $eventsForNow = $this->aggregateRepository->saveAggregate(
            $command->getAggregateId(), $handlerAndAggregate->getAggregate(), $dispatchResult->getEventsForNow());
        return $dispatchResult->withEventsForNow($eventsForNow);
    }

    private function dispatchCommandAndSaveAggregate(CommandWithMetadata $command): SideEffects
    {
        return $this->concurrentProofFunctionCaller->executeFunction(function () use ($command) {
            return $this->tryDispatchCommandAndSaveAggregate($command);
        });
    }

    private function loadCommandHandlerAndAggregate(CommandWithMetadata $command): CommandHandlerAndAggregate
    {
        $handler = $this->commandSubscriber->getHandlerForCommand($command->getCommand());
        $aggregate = $this->aggregateRepository->loadAggregate(
            new AggregateDescriptor($command->getAggregateId(), $handler->getHandlerClass())
        );
        return new CommandHandlerAndAggregate($handler, $aggregate);
    }

    private function decorateEventWithMetaData($event, MetaData $metaData): EventWithMetaData
    {
        return new EventWithMetaData($event, $metaData->withEventId(Guid::generate()));
    }

    private function applyCommandAndReturnSideEffects(CommandWithMetadata $command, CommandHandlerAndAggregate $handlerAndAggregate)
    {
        $aggregate = $handlerAndAggregate->getAggregate();
        $handler = $handlerAndAggregate->getCommandHandler();
        $metaData = $this->eventMetadataFactory->factoryEventMetadata($command, $aggregate);
        $newMessageGenerator = $this->commandApplier->applyCommand($aggregate, $command->getCommand(), $handler->getMethodName());
        /** @var EventWithMetaData[] $eventsWithMetaData */
        $eventsWithMetaData = [];
        /** @var ScheduledCommand[] $scheduledCommands */
        $scheduledCommands = [];
        foreach ($newMessageGenerator as $message) {
            if ($this->isScheduledCommand($message)) {
                $scheduledCommands[] = $message;
            } else {
                $eventWithMetaData = $this->decorateEventWithMetaData($message, $metaData);
                $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, [$eventWithMetaData]);
                $eventsWithMetaData[] = $eventWithMetaData;
            }
        }
        return new SideEffects(
            new AggregateDescriptor($command->getAggregateId(), $handler->getHandlerClass()),
            $eventsWithMetaData,
            $scheduledCommands
        );
    }

    private function isScheduledCommand($message): bool
    {
        return $message instanceof ScheduledCommand;
    }
}