<?php


namespace Dudulina\Scheduling;


use Dudulina\Command\CommandMetadata;

interface CommandScheduler
{
    public function scheduleCommand(ScheduledCommand $scheduledCommand, string $aggregateClass, $aggregateId, CommandMetadata $commandMetadata = null);

    public function cancelCommand($commandId);
}