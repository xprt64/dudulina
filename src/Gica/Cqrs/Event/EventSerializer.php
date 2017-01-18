<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


class EventSerializer
{
    public function serializeEvent(\Gica\Cqrs\Event $event)
    {
        return serialize($event);
    }

    public function deserializeEvent($eventClass, $eventPayload): \Gica\Cqrs\Event
    {
        return unserialize($eventPayload);
    }
}