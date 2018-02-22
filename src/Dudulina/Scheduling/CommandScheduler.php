<?php


namespace Dudulina\Scheduling;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Command\CommandMetadata;

interface CommandScheduler
{
    public function scheduleCommand(ScheduledCommand $scheduledCommand, AggregateDescriptor $aggregateDescriptor, CommandMetadata $commandMetadata = null);

    public function cancelCommand($commandId);
}