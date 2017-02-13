<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;


use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Types\Guid;

interface EventStore
{
    public function loadEventsForAggregate(string $aggregateClass, $aggregateId): EventStore\AggregateEventStream;

    /**
     * @param Guid $aggregateId
     * @param string $aggregateClass
     * @param EventWithMetaData[] $eventsWithMetaData
     * @param int $expectedVersion
     * @param int $expectedSequence
     * @return
     */
    public function appendEventsForAggregate($aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence);

    public function loadEventsByClassNames(array $eventClasses): EventStore\EventStream;

    public function getAggregateVersion(string $aggregateClass, $aggregateId);

    public function fetchLatestSequence(): int;
}