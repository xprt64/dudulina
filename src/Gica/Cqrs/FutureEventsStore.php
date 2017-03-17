<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;

use Gica\Cqrs\Event\EventWithMetaData;

interface FutureEventsStore
{
    public function loadAndProcessScheduledEvents(callable $eventProcessor /** function(ScheduledEventWithMetadata) */);

    /**
     * @param EventWithMetaData[] $eventWithMetaData
     */
    public function scheduleEvents($eventWithMetaData);

    public function scheduleEvent(EventWithMetaData $eventWithMetaData, \DateTimeImmutable $date);
}