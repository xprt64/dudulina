<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command\CommandTester;

use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandApplier;
use Gica\Cqrs\Command\CommandSubscriber;
use Gica\Cqrs\Command\CommandWithMetadata;
use Gica\Cqrs\Command\MetadataWrapper as CommandMetadataFactory;
use Gica\Cqrs\Command\ValueObject\CommandHandlerAndAggregate;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Event\MetadataFactory as EventMetadataFactory;
use Gica\Cqrs\Event\ScheduledEvent;
use Gica\Cqrs\Scheduling\ScheduledCommand;
use Gica\Types\Guid;

class DefaultCommandTesterWithExplanation implements Command\CommandTesterWithExplanation
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

    public function whyCantExecuteCommand(Command $command)
    {
        try {
            $command = $this->commandMetadataFactory->wrapCommandWithMetadata($command, null);
            $this->applyCommand($command, $this->loadCommandHandlerAndAggregate($command));
            return [];
        } catch (\Exception $exception) {
            return [$exception];
        }
    }

    private function loadCommandHandlerAndAggregate(CommandWithMetadata $command): CommandHandlerAndAggregate
    {
        return new CommandHandlerAndAggregate(
            $this->commandSubscriber->getHandlerForCommand($command->getCommand()),
            $this->aggregateRepository->loadAggregate($this->commandSubscriber->getHandlerForCommand($command->getCommand())->getHandlerClass(), $command->getAggregateId())
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