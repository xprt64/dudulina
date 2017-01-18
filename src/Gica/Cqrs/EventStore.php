<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;


interface EventStore
{
    public function loadEventsForAggregate(string $aggregateClass, \Gica\Types\Guid $aggregateId): EventStore\AggregateEventStream;

    /**
     * @param \Gica\Types\Guid $aggregateId
     * @param string $aggregateClass
     * @param \Gica\Cqrs\Event\EventWithMetaData[] $eventsWithMetaData
     * @param int $expectedVersion
     * @return
     */
    public function appendEventsForAggregate(\Gica\Types\Guid $aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence);

    public function loadEventsByClassNames(array $eventClasses): EventStore\EventStream;

    public function getAggregateVersion(string $aggregateClass, \Gica\Types\Guid $aggregateId);

    public function fetchLatestSequence() : int;
}