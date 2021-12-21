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
    public function __construct(
        private CommandSubscriber $commandSubscriber,
        private CommandApplier $commandApplier,
        private AggregateRepository $aggregateRepository,
        private ConcurrentProofFunctionCaller $concurrentProofFunctionCaller,
        private EventsApplierOnAggregate $eventsApplierOnAggregate,
        private EventMetadataFactory $eventMetadataFactory,
        private CommandMetadataFactory $commandMetadataFactory,
        private SideEffectsDispatcher $sideEffectsDispatcher
    )
    {
    }

    public function dispatchCommand(Command $command, array $metadata = null): SideEffects
    {
        $sideEffects = $this->dispatchCommandAndSaveAggregate(
            $this->commandMetadataFactory->wrapCommandWithMetadata($command, $metadata)
        )
            ->withCommandMetadata($metadata);
        $this->sideEffectsDispatcher->dispatchSideEffects($sideEffects);
        return $sideEffects;
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