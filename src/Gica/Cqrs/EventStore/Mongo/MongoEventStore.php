<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;


use MongoDB\BSON\ObjectID;

class MongoEventStore implements \Gica\Cqrs\EventStore
{
    const EVENTS_EVENT_CLASS = 'events.eventClass';
    const EVENT_CLASS = 'eventClass';

    /** @var  \MongoDB\Collection */
    protected $collection;
    /**
     * @var \Gica\Cqrs\EventStore\Mongo\EventSerializer
     */
    private $eventSerializer;

    public function __construct(
        \MongoDB\Collection $collection,
        \Gica\Cqrs\EventStore\Mongo\EventSerializer $eventSerializer
    )
    {
        $this->collection = $collection;
        $this->eventSerializer = $eventSerializer;
    }

    public function loadEventsForAggregate(string $aggregateClass, \Gica\Types\Guid $aggregateId): \Gica\Cqrs\EventStore\AggregateEventStream
    {
        return new MongoAggregateAllEventStream(
            $this->collection,
            $aggregateClass,
            $aggregateId,
            $this->eventSerializer);
    }

    public function createStore()
    {
        $this->collection->createIndex(['aggregateId' => 1, 'aggregateClass' => 1, 'version' => 1], ['unique' => true]);
        $this->collection->createIndex(['aggregateId' => 1, 'aggregateClass' => 1, 'version' => -1]);
        $this->collection->createIndex(['aggregateId' => 1, 'aggregateClass' => 1, 'version' => 1, 'sequence' => 1]);
        $this->collection->createIndex([self::EVENTS_EVENT_CLASS => 1, 'sequence' => 1]);
        $this->collection->createIndex(['sequence' => -1], ['unique' => true]);
        $this->collection->createIndex(['sequence' => 1], ['unique' => true]);
    }

    public function dropStore()
    {
        $this->collection->drop();
    }

    public function appendEventsForAggregate(\Gica\Types\Guid $aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence)
    {
        if (!$eventsWithMetaData) {
            return;
        }

        $firstEventWithMetaData = reset($eventsWithMetaData);

        try {
            $authenticatedUserId = $firstEventWithMetaData->getMetaData()->getAuthenticatedUserId();
            $this->collection->insertOne([
                'aggregateId'         => new \MongoDB\BSON\ObjectID((string)$aggregateId),
                'aggregateClass'      => $aggregateClass,
                'version'             => 1 + $expectedVersion,
                'sequence'            => 1 + $expectedSequence,
                'createdAt'           => new \MongoDB\BSON\UTCDateTime(microtime(true) * 1000),
                'authenticatedUserId' => $authenticatedUserId ? new ObjectID((string)$authenticatedUserId) : null,
                'events'              => $this->packEvents($eventsWithMetaData),
            ]);
        } catch (\MongoDB\Driver\Exception\BulkWriteException $bulkWriteException) {
            throw new \Gica\Cqrs\EventStore\Exception\ConcurrentModificationException($bulkWriteException->getMessage());
        }
    }

    private function packEvents($events):array
    {
        return array_map([$this, 'packEvent'], $events);
    }

    private function packEvent(\Gica\Cqrs\Event\EventWithMetaData $eventWithMetaData):array
    {
        ob_start();
        var_dump($eventWithMetaData->getEvent());
        $dump = ob_get_clean();

        return array_merge([
            self::EVENT_CLASS => get_class($eventWithMetaData->getEvent()),
            'payload'         => $this->eventSerializer->serializeEvent($eventWithMetaData->getEvent()),
            'dump'            => $dump,
        ]);
    }

    public function loadEventsByClassNames(array $eventClasses): \Gica\Cqrs\EventStore\EventStream
    {
        return new MongoAllEventByClassesStream(
            $this->collection,
            $eventClasses,
            $this->eventSerializer);
    }

    public function getAggregateVersion(string $aggregateClass, \Gica\Types\Guid $aggregateId)
    {
        return (new \Gica\Cqrs\EventStore\Mongo\LastAggregateVersionFetcher())->fetchLatestVersion($this->collection, $aggregateClass, $aggregateId);
    }

    public function fetchLatestSequence():int
    {
        return (new \Gica\Cqrs\EventStore\Mongo\LastAggregateSequenceFetcher())->fetchLatestSequence($this->collection);
    }

}