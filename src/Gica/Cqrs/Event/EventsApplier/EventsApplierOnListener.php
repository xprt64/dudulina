<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventsApplier;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;

class EventsApplierOnListener
{
    public function applyEventsOnListener($listener, $eventsWithMetaData)
    {
        /** @var EventWithMetaData $eventWithMetaData */
        foreach ($eventsWithMetaData as $eventWithMetaData) {
             $this->applyEvent($listener, $eventWithMetaData);
        }
    }

    private function applyEvent($listener, EventWithMetaData $eventWithMetaData)
    {
        $methodName = $this->getMethodName($eventWithMetaData->getEvent());

        call_user_func([$listener, $methodName], $eventWithMetaData->getEvent(), $eventWithMetaData->getMetaData());
    }

    private function getMethodName(Event $event)
    {
        $parts = explode('\\', get_class($event));

        return 'on' . end($parts);
    }
}