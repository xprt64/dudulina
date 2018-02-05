<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventProcessing;


use Gica\Selector\Selectable;

interface EventProcessingTracker
{
    public function isEventProcessingAlreadyStarted(string $processId, string $eventId): bool;

    public function isEventProcessingAlreadyEnded(string $processId, string $eventId): bool;

    /**
     * @param string $processId
     * @return InProgressProcessingEvent[]|Selectable
     */
    public function getAllInProgressProcessingEvents(string $processId);

    /**
     * @param string $processId
     * @param string $eventId
     * @return void
     * @throws ConcurentEventProcessingException
     */
    public function startProcessingEvent(string $processId, string $eventId);

    public function endProcessingEvent(string $processId, string $eventId);

    public function clearProcessingEvent(string $processId, string $eventId);
}