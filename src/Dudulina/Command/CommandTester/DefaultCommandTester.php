<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandTester;

use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\CommandTester;
use Dudulina\Command\CommandWithMetadata;
use Dudulina\Command\MetadataWrapper as CommandMetadataFactory;
use Dudulina\Command\ValueObject\CommandHandlerAndAggregate;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\Event\MetadataFactory as EventMetadataFactory;
use Dudulina\Event\ScheduledEvent;
use Dudulina\Scheduling\ScheduledCommand;
use Gica\Types\Guid;

class DefaultCommandTester implements CommandTester
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

    public function __construct(
        CommandSubscriber $commandSubscriber,
        CommandApplier $commandApplier,
        AggregateRepository $aggregateRepository,
        EventsApplierOnAggregate $eventsApplier,
        EventMetadataFactory $eventMetadataFactory,
        CommandMetadataFactory $commandMetadataFactory
    )
    {
        $this->commandSubscriber = $commandSubscriber;
        $this->commandApplier = $commandApplier;
        $this->aggregateRepository = $aggregateRepository;
        $this->eventsApplierOnAggregate = $eventsApplier;
        $this->eventMetadataFactory = $eventMetadataFactory;
        $this->commandMetadataFactory = $commandMetadataFactory;
    }

    public function canExecuteCommand(Command $command): bool
    {
        try {
            $command = $this->commandMetadataFactory->wrapCommandWithMetadata($command, null);
            $this->applyCommand($command, $this->loadCommandHandlerAndAggregate($command));
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function loadCommandHandlerAndAggregate(CommandWithMetadata $command): CommandHandlerAndAggregate
    {
        return new CommandHandlerAndAggregate(
            $this->commandSubscriber->getHandlerForCommand($command->getCommand()),
            $this->aggregateRepository->loadAggregate(
                new AggregateDescriptor(
                    $command->getAggregateId(),
                    $this->commandSubscriber->getHandlerForCommand($command->getCommand())
                        ->getHandlerClass()
                )
            )
        );
    }

    private function decorateEventWithMetaData($event, MetaData $metaData): EventWithMetaData
    {
        return new EventWithMetaData($event, $metaData->withEventId(Guid::generate()));
    }

    /**
     * @param CommandWithMetadata $command
     * @param CommandHandlerAndAggregate $handlerAndAggregate
     * @return void
     */
    private function applyCommand(CommandWithMetadata $command, CommandHandlerAndAggregate $handlerAndAggregate)
    {
        $aggregate = $handlerAndAggregate->getAggregate();
        $handler = $handlerAndAggregate->getCommandHandler();

        $metaData = $this->eventMetadataFactory->factoryEventMetadata($command, $aggregate);

        $newMessageGenerator = $this->commandApplier->applyCommand($aggregate, $command->getCommand(), $handler->getMethodName());

        foreach ($newMessageGenerator as $message) {
            if (!$this->isScheduledCommand($message)) {
                $eventWithMetaData = $this->decorateEventWithMetaData($message, $metaData);
                if (!$this->isScheduledEvent($message)) {
                    $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, [$eventWithMetaData]);
                }
            }
        }
    }

    private function isScheduledEvent($event): bool
    {
        return $event instanceof ScheduledEvent;
    }

    private function isScheduledCommand($message): bool
    {
        return $message instanceof ScheduledCommand;
    }
}