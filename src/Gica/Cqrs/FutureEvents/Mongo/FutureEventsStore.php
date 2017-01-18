<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\FutureEvents\Mongo;


class FutureEventsStore implements \Gica\Cqrs\FutureEventsStore
{

    /* @var \MongoDB\Collection */
    private $collection;

    public function __construct(
        \MongoDB\Collection $collection
    )
    {
        $this->collection = $collection;
    }

    public function loadAndProcessScheduledEvents(callable $eventProcessor)
    {
        $scheduledEvents = $this->loadScheduledEvents();

        foreach ($scheduledEvents as $scheduledEvent) {
            $eventProcessor($scheduledEvent);

            $this->markEventAsProcessed($scheduledEvent);
        }
    }

    private function loadScheduledEvents()
    {
        $cursor = $this->collection->find([
            'scheduleAt' => [
                '$lte' => new \MongoDB\BSON\UTCDateTime(time() * 1000),
            ],
        ], [
            'scheduleAt' => '1',
        ]);

        return (new \Gica\Iterator\IteratorTransformer\IteratorMapper(function ($document) {
            return $this->extractEventWithData($document);
        }))($cursor);
    }

    /**
     * @param \Gica\Cqrs\Event\EventWithMetaData[] $futureEventsWithMetaData
     */
    public function scheduleEvents($futureEventsWithMetaData)
    {
        foreach ($futureEventsWithMetaData as $eventWithMetaData) {
            /** @var $event \Gica\Cqrs\Event\FutureEvent */
            $event = $eventWithMetaData->getEvent();
            $this->scheduleEvent($eventWithMetaData, $event->getFireDate());
        }
    }

    private function extractEventWithData($document)
    {
        return new \Gica\Cqrs\FutureEvents\ScheduledEvent(
            $document['_id'],
            \unserialize($document['eventWithMetaData']));
    }

    private function markEventAsProcessed(\Gica\Cqrs\FutureEvents\ScheduledEvent $scheduledEvent)
    {
        $this->collection->deleteOne([
            '_id' => new \MongoDB\BSON\ObjectID($scheduledEvent->getEventId()),
        ]);
    }

    public function scheduleEvent(\Gica\Cqrs\Event\EventWithMetaData $eventWithMetaData, \DateTimeImmutable $date)
    {
        $this->collection->insertOne([
            '_id'               => new \MongoDB\BSON\ObjectID(),
            'scheduleAt'        => new \MongoDB\BSON\UTCDateTime($date->getTimestamp() * 1000),
            'eventWithMetaData' => \serialize($eventWithMetaData),
        ]);
    }

    public function createStore()
    {
        $this->collection->createIndex(['scheduleAt' => 1, 'version' => 1]);
    }
}