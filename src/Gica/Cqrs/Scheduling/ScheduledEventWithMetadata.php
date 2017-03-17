<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Scheduling;


use Gica\Cqrs\Event\EventWithMetaData;

class ScheduledEventWithMetadata
{
    private $eventId;
    /**
     * @var EventWithMetaData
     */
    private $eventWithMetaData;

    public function __construct(
        $eventId,
        EventWithMetaData $eventWithMetaData
    )
    {
        $this->eventId = $eventId;
        $this->eventWithMetaData = $eventWithMetaData;
    }

    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return EventWithMetaData
     */
    public function getEventWithMetaData(): EventWithMetaData
    {
        return $this->eventWithMetaData;
    }
}