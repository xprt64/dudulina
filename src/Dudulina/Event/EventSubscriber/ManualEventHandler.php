<?php
/******************************************************************************
 * Copyright (c) 2018 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/
namespace Dudulina\Event\EventSubscriber;

use Dudulina\Event\EventSubscriber;

class ManualEventHandler implements EventSubscriber
{
    private $callbacks = [];

    public function subscribeCallbackToEvent(string $eventClass, $callback)
    {
        $this->callbacks[$eventClass][] = $callback;
    }

    public function subscribeCallbackToEvents(array $eventClasses, $callback)
    {
        foreach ($eventClasses as $eventClass) {
            $this->subscribeCallbackToEvent($eventClass, $callback);
        }
    }

    /**
     * @inheritdoc
     */
    public function getListenersForEvent($event)
    {
        return $this->callbacks[\get_class($event)] ?? [];
    }
}