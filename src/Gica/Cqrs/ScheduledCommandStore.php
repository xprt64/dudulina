<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;

use Gica\Cqrs\Scheduling\ScheduledCommand;

interface ScheduledCommandStore
{
    public function loadAndProcessScheduledCommands(callable $eventProcessor/** function(ScheduledCommand $scheduledCommand) */);

    /**
     * @param ScheduledCommand[] $scheduledCommands
     */
    public function scheduleCommands($scheduledCommands);

    public function cancelCommand($commandId);
}