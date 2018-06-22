<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);


namespace Dudulina\Aggregate;


use Dudulina\Event\EventWithMetaData;

interface AggregateRepository
{
    /**
     * @param AggregateDescriptor $aggregateDescriptor
     * @return object Aggregate
     */
    public function loadAggregate(AggregateDescriptor $aggregateDescriptor);

    /**
     * @param $aggregateId
     * @param $aggregate
     * @param EventWithMetaData[] $newEventsWithMeta
     * @return EventWithMetaData[] decorated events with version and index
     */
    public function saveAggregate($aggregateId, $aggregate, $newEventsWithMeta);
}