<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore\AggregateEventStream;
use Dudulina\EventStore\EventStream;

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

    public function loadEventsByClassNames(array $eventClasses): EventStream;

    public function getAggregateVersion(AggregateDescriptor $aggregateDescriptor);

    public function findEventById(string $eventId): ?EventWithMetaData;
}