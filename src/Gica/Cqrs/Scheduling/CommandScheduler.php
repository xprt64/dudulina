<?php


namespace Gica\Cqrs\Scheduling;


use Gica\Cqrs\Command\CommandMetadata;

interface CommandScheduler
{
    public function scheduleCommand(ScheduledCommand $scheduledCommand, string $aggregateClass, $aggregateId, CommandMetadata $commandMetadata = null);

    public function cancelCommand($commandId);
}