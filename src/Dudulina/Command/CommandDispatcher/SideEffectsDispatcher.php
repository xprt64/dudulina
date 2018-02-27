<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandDispatcher;


use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher\SideEffects;
use Dudulina\Event\EventDispatcher;
use Dudulina\Scheduling\CommandScheduler;

class SideEffectsDispatcher
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var CommandScheduler|null
     */
    private $commandScheduler;

    public function __construct(
        EventDispatcher $eventDispatcher,
        ?CommandScheduler $commandScheduler = null
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->commandScheduler = $commandScheduler;
    }

    public function dispatchSideEffects(SideEffects $sideEffects)
    {
        foreach ($sideEffects->getEventsForNow() as $eventWithMetaData) {
            $this->eventDispatcher->dispatchEvent($eventWithMetaData);
        }

        if ($this->commandScheduler && !empty($sideEffects->getScheduledCommands())) {
            foreach ($sideEffects->getScheduledCommands() as $scheduledCommand) {
                $this->commandScheduler->scheduleCommand($scheduledCommand, $sideEffects->getAggregateDescriptor(), $sideEffects->getCommandMetadata());
            }
        }
    }
}