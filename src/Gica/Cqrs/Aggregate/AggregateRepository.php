<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Aggregate;

interface AggregateRepository
{
    public function loadAggregate(string $aggregateClass, \Gica\Types\Guid $aggregateId);

    /**
     * @param \Gica\Types\Guid $aggregateId
     * @param $aggregate
     * @param \Gica\Cqrs\Event\EventWithMetaData[] $newEventsWithMetaData
     * @return
     */
    public function saveAggregate(\Gica\Types\Guid $aggregateId, $aggregate, $newEventsWithMetaData);
}