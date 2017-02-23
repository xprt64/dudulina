<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;


use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore\AggregateEventStream;
use Gica\Cqrs\EventStore\EventStreamGroupedByCommit;

interface EventStore
{
    public function loadEventsForAggregate(string $aggregateClass, $aggregateId): AggregateEventStream;

    /**
     * @param $aggregateId
     * @param string $aggregateClass
     * @param EventWithMetaData[] $eventsWithMetaData
     * @param int $expectedVersion
     * @param int $expectedSequence
     * @return
     */
    public function appendEventsForAggregate($aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence);

    public function loadEventsByClassNames(array $eventClasses): EventStreamGroupedByCommit;

    public function getAggregateVersion(string $aggregateClass, $aggregateId);

    public function fetchLatestSequence(): int;
}