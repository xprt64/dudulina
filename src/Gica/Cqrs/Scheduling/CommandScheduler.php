<?php


namespace Gica\Cqrs\Scheduling;


interface CommandScheduler
{
    public function scheduleCommand(ScheduledCommand $scheduledCommand, string $aggregateClass, $aggregateId, $commandMetadata);

    public function cancelCommand($commandId);
}