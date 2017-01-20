<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventsApplier;


class EventsApplierOnListener
{
    public function applyEventsOnListener($listener, $eventsWithMetaData)
    {
        /** @var \Gica\Cqrs\Event\EventWithMetaData $eventWithMetaData */
        foreach ($eventsWithMetaData as $eventWithMetaData) {
             $this->applyEvent($listener, $eventWithMetaData);
        }
    }

    private function applyEvent($listener, \Gica\Cqrs\Event\EventWithMetaData $eventWithMetaData)
    {
        $methodName = $this->getMethodName($eventWithMetaData->getEvent());

        call_user_func([$listener, $methodName], $eventWithMetaData->getEvent(), $eventWithMetaData->getMetaData());
    }

    private function getMethodName(\Gica\Cqrs\Event $event)
    {
        $parts = explode('\\', get_class($event));

        return 'on' . end($parts);
    }
}