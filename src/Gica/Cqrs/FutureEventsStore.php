<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;

interface FutureEventsStore
{
    public function loadAndProcessScheduledEvents(callable $eventProcessor /** function(ScheduledEvent) */);

    /**
     * @param \Gica\Cqrs\Event\EventWithMetaData[] $futureEventsWithMetaData
     */
    public function scheduleEvents($futureEventsWithMetaData);

    public function scheduleEvent(Event\EventWithMetaData $eventWithMetaData, \DateTimeImmutable $date);
}