<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Aggregate;


use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\AggregateEventStream;
use Gica\Cqrs\EventStore\EventStream;
use Gica\Types\Guid;

class AggregateRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventsApplierOnAggregate
     */
    private $eventsApplierOnAggregate;

    /**
     * @var EventStream[]
     */
    private $aggregateToEventStreamMap;

    public function __construct(
        EventStore $eventStore,
        EventsApplierOnAggregate $eventsApplierOnAggregate
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplierOnAggregate = $eventsApplierOnAggregate;
        $this->aggregateToEventStreamMap = new \SplObjectStorage();
    }

    public function loadAggregate(string $aggregateClass, $aggregateId)
    {
        $aggregate = new $aggregateClass;

        $priorEvents = $this->eventStore->loadEventsForAggregate($aggregateClass, $aggregateId);

        $this->aggregateToEventStreamMap[$aggregate] = $priorEvents;

        /** @var EventWithMetaData[] $priorEvents */
        $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, $priorEvents);

        return $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function saveAggregate($aggregateId, $aggregate, $newEventsWithMetaData)
    {
        /** @var AggregateEventStream $priorEvents */
        $priorEvents = $this->aggregateToEventStreamMap[$aggregate];

        $this->eventStore->appendEventsForAggregate(
            $aggregateId, get_class($aggregate), $newEventsWithMetaData, $priorEvents->getVersion(), $priorEvents->getSequence());
    }
}