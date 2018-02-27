<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Command\CommandMetadata;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Scheduling\ScheduledCommand;

class SideEffects
{
    /** @var EventWithMetaData[] */
    private $eventsForNow;

    /** @var ScheduledCommand[] */
    private $scheduledCommands;

    /** @var AggregateDescriptor */
    private $aggregateDescriptor;

    /** @var CommandMetadata */
    private $commandMetadata;

    /**
     * @param EventWithMetaData[] $eventsForNow
     * @param ScheduledCommand[] $scheduledCommands
     * @param AggregateDescriptor $aggregateDescriptor
     */
    public function __construct(AggregateDescriptor $aggregateDescriptor, array $eventsForNow, array $scheduledCommands)
    {
        $this->eventsForNow = $eventsForNow;
        $this->scheduledCommands = $scheduledCommands;
        $this->aggregateDescriptor = $aggregateDescriptor;
    }

    /**
     * @return EventWithMetaData[]
     */
    public function getEventsForNow(): array
    {
        return $this->eventsForNow;
    }

    /**
     * @param EventWithMetaData[] $eventsForNow
     * @return SideEffects
     */
    public function withEventsForNow(array $eventsForNow): self
    {
        $other = clone $this;
        $other->eventsForNow = $eventsForNow;
        return $other;
    }

    public function withCommandMetadata(?CommandMetadata $commandMetadata): self
    {
        $other = clone $this;
        $other->commandMetadata = $commandMetadata;
        return $other;
    }

    public function getCommandMetadata(): ?CommandMetadata
    {
        return $this->commandMetadata;
    }

    /**
     * @return ScheduledCommand[]
     */
    public function getScheduledCommands(): array
    {
        return $this->scheduledCommands;
    }

    public function getAggregateDescriptor(): AggregateDescriptor
    {
        return $this->aggregateDescriptor;
    }


}