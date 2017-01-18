<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Aggregate;


use Gica\Cqrs\EventStore\AggregateEventStream;

class AggregateRepositoryDefault implements \Gica\Cqrs\Aggregate\AggregateRepository
{
    /**
     * @var \Gica\Cqrs\EventStore
     */
    private $eventStore;

    /**
     * @var \Gica\Cqrs\Event\EventsApplierOnAggregate
     */
    private $eventsApplierOnAggregate;

    /**
     * @var \Gica\Cqrs\EventStore\EventStream[]
     */
    private $aggregateToEventStreamMap;

    public function __construct(
        \Gica\Cqrs\EventStore $eventStore,
        \Gica\Cqrs\Event\EventsApplierOnAggregate $eventsApplierOnAggregate
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplierOnAggregate = $eventsApplierOnAggregate;
        $this->aggregateToEventStreamMap = new \SplObjectStorage();
    }

    public function loadAggregate(string $aggregateClass, \Gica\Types\Guid $aggregateId)
    {
        $aggregate = new $aggregateClass;

        $priorEvents = $this->eventStore->loadEventsForAggregate($aggregateClass, $aggregateId);

        $this->aggregateToEventStreamMap[$aggregate] = $priorEvents;

        /** @var \Gica\Cqrs\Event\EventWithMetaData[] $priorEvents */
        $this->eventsApplierOnAggregate->applyEventsOnAggregate($aggregate, $priorEvents);

        return $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function saveAggregate(\Gica\Types\Guid $aggregateId, $aggregate, $newEventsWithMetaData)
    {
        /** @var AggregateEventStream $priorEvents */
        $priorEvents = $this->aggregateToEventStreamMap[$aggregate];

        $this->eventStore->appendEventsForAggregate(
            $aggregateId, get_class($aggregate), $newEventsWithMetaData, $priorEvents->getVersion(), $priorEvents->getSequence());
    }
}