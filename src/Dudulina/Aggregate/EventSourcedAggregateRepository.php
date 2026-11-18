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
    private \SplObjectStorage $aggregateToEventStreamMap;

    public function __construct(
        private EventStore $eventStore,
        private EventsApplierOnAggregate $eventsApplier
    )
    {
        $this->aggregateToEventStreamMap = new \SplObjectStorage();
    }

    #[\Override]
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
    #[\Override]
    public function saveAggregate($aggregateId, $aggregate, $newEventsWithMeta)
    {
        /** @var AggregateEventStream $priorEvents */
        $priorEvents = $this->aggregateToEventStreamMap[$aggregate];

        $this->eventStore->appendEventsForAggregate(
            new AggregateDescriptor($aggregateId, $aggregate::class), $newEventsWithMeta, $priorEvents
        );

        return array_map(fn(EventWithMetaData $event) => $event->withVersion($priorEvents->getVersion() + 1), $newEventsWithMeta);
    }
}