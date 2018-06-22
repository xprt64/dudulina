<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Aggregate;


use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\AggregateEventStream;

class EventSourcedAggregateRepository implements AggregateRepository
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

    public function loadAggregate(AggregateDescriptor $aggregateDescriptor)
    {
        $aggregateClass = $aggregateDescriptor->getAggregateClass();

        $aggregate = new $aggregateClass;

        $priorEvents = $this->eventStore->loadEventsForAggregate($aggregateDescriptor);

        $this->aggregateToEventStreamMap[$aggregate] = $priorEvents;

        /** @var EventWithMetaData[] $priorEvents */
        $this->eventsApplier->applyEventsOnAggregate($aggregate, $priorEvents);

        return $aggregate;
    }

    /**
     * @param $aggregateId
     * @param $aggregate
     * @param EventWithMetaData[] $newEventsWithMeta
     * @return EventWithMetaData[] decorated events with version and index
     */
    public function saveAggregate($aggregateId, $aggregate, $newEventsWithMeta)
    {
        /** @var AggregateEventStream $priorEvents */
        $priorEvents = $this->aggregateToEventStreamMap[$aggregate];

        $this->eventStore->appendEventsForAggregate(
            new AggregateDescriptor($aggregateId, \get_class($aggregate)), $newEventsWithMeta, $priorEvents
        );

        return array_map(function (EventWithMetaData $event) use ($priorEvents) {
            return $event->withVersion($priorEvents->getVersion() + 1);
        }, $newEventsWithMeta);
    }
}