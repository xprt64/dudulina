<?php


namespace Dudulina\Scheduling;


use Dudulina\Aggregate\AggregateDescriptor;

interface CommandScheduler
{
    public function scheduleCommand(ScheduledCommand $scheduledCommand, AggregateDescriptor $aggregateDescriptor, $commandMetadata = null);

    public function cancelCommand($commandId);
}