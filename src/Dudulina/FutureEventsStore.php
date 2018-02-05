<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina;

use Dudulina\Event\EventWithMetaData;

interface FutureEventsStore
{
    public function loadAndProcessScheduledEvents(callable $eventProcessor /** function(ScheduledEventWithMetadata) */);

    /**
     * @param EventWithMetaData[] $eventWithMetaData
     */
    public function scheduleEvents($eventWithMetaData);

    public function scheduleEvent(EventWithMetaData $eventWithMetaData, \DateTimeImmutable $date);
}