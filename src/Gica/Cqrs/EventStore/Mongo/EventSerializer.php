<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;


use Gica\Cqrs\Event;

class EventSerializer
{
    public function serializeEvent(Event $event)
    {
        return serialize($event);
    }

    public function deserializeEvent($eventClass, $eventPayload): Event
    {
        return unserialize($eventPayload);
    }
}