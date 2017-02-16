<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventSubscriber;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventSubscriber;

class ManualEventSubscriber implements EventSubscriber
{
    private $eventListeners = [];

    public function subscribeToEvent($listener)
    {
        $this->eventListeners[] = $listener;
    }

    /**
     * @param $event
     * @return callable[]
     */
    public function getListenersForEvent(Event $event)
    {
        $result = [];

        foreach ($this->eventListeners as $eventListener) {
            $methodName = $this->getMethodName($event);

            $method = [$eventListener, $methodName];

            if (is_callable($method)) {
                $result[] = $method;
            }
        }

        return $result;
    }

    private function getMethodName($event)
    {
        $parts = explode('\\', get_class($event));

        return 'handle' . end($parts);
    }

}