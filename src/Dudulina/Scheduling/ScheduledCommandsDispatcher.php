<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Scheduling;


use Dudulina\Command\CommandDispatcher;

class ScheduledCommandsDispatcher
{
    /**
     * @var ScheduledCommandStore
     */
    private $store;
    /**
     * @var CommandDispatcher
     */
    private $dispatcher;

    public function __construct(
        ScheduledCommandStore $store,
        CommandDispatcher $dispatcher
    )
    {
        $this->store = $store;
        $this->dispatcher = $dispatcher;
    }

    public function run()
    {
        $this->store->loadAndProcessScheduledCommands(function (ScheduledCommand $scheduledCommand) {
            $this->dispatcher->dispatchCommand($scheduledCommand);
        });
    }
}