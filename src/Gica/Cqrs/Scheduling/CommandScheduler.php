<?php


namespace Gica\Cqrs\Scheduling;


interface CommandScheduler
{

    /**
     * @param ScheduledCommand[] $scheduledCommands
     */
    public function scheduleCommands($scheduledCommands);

    public function cancelCommand($commandId);
}