<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\FutureEvents;


class ScheduledEvent
{
    private $eventId;
    /**
     * @var \Gica\Cqrs\Event\EventWithMetaData
     */
    private $eventWithMetaData;

    public function __construct(
        $eventId,
        \Gica\Cqrs\Event\EventWithMetaData $eventWithMetaData
    )
    {
        $this->eventId = $eventId;
        $this->eventWithMetaData = $eventWithMetaData;
    }

    /**
     * @return mixed
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return \Gica\Cqrs\Event\EventWithMetaData
     */
    public function getEventWithMetaData(): \Gica\Cqrs\Event\EventWithMetaData
    {
        return $this->eventWithMetaData;
    }
}