<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Scheduling;

interface ScheduledCommandStore
{
    public function loadAndProcessScheduledCommands(callable $eventProcessor/** function(ScheduledCommand $scheduledCommand) */);
}