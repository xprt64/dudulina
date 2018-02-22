<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore\AggregateEventStream;
use Dudulina\EventStore\EventStreamGroupedByCommit;

interface EventStore
{
    public function loadEventsForAggregate(AggregateDescriptor $aggregateDescriptor): AggregateEventStream;

    /**
     * @param AggregateDescriptor $aggregateDescriptor
     * @param EventWithMetaData[] $eventsWithMetaData
     * @param AggregateEventStream $expectedEventStream
     * @return void
     */
    public function appendEventsForAggregate(AggregateDescriptor $aggregateDescriptor, $eventsWithMetaData, AggregateEventStream $expectedEventStream):void;

    public function loadEventsByClassNames(array $eventClasses): EventStreamGroupedByCommit;

    public function getAggregateVersion(AggregateDescriptor $aggregateDescriptor);

    public function fetchLatestSequence(): int;

    public function findEventById(string $eventId): ?EventWithMetaData;
}