<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Aggregate;


use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\AggregateEventStream;

class AggregateRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventsApplierOnAggregate
     */
    private $eventsApplier;

    /**
     * @var \SplObjectStorage
     */
    private $aggregateToEventStreamMap;

    public function __construct(
        EventStore $eventStore,
        EventsApplierOnAggregate $eventsApplier
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplier = $eventsApplier;
        $this->aggregateToEventStreamMap = new \SplObjectStorage();
    }

    public function loadAggregate(string $aggregateClass, $aggregateId)
    {
        $aggregate = new $aggregateClass;

        $priorEvents = $this->eventStore->loadEventsForAggregate($aggregateClass, $aggregateId);

        $this->aggregateToEventStreamMap[$aggregate] = $priorEvents;

        /** @var EventWithMetaData[] $priorEvents */
        $this->eventsApplier->applyEventsOnAggregate($aggregate, $priorEvents);

        return $aggregate;
    }

    /**
     * @param $aggregateId
     * @param $aggregate
     * @param EventWithMetaData[] $newEventsWithMeta
     */
    public function saveAggregate($aggregateId, $aggregate, $newEventsWithMeta)
    {
        /** @var AggregateEventStream $priorEvents */
        $priorEvents = $this->aggregateToEventStreamMap[$aggregate];

        $this->eventStore->appendEventsForAggregate(
            $aggregateId, get_class($aggregate), $newEventsWithMeta, $priorEvents->getVersion(), $priorEvents->getSequence());
    }
}