<?php


namespace Gica\Cqrs\EventStore\InMemory;


use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\AggregateEventStream;
use Gica\Cqrs\EventStore\EventStream;
use Gica\Cqrs\EventStore\Exception\ConcurrentModificationException;

class InMemoryEventStore implements EventStore
{
    public $events = [];
    private $versions = [];
    private $latestSequence = 0;

    public function loadEventsForAggregate(string $aggregateClass, $aggregateId): AggregateEventStream
    {
        return new InMemoryAggregateEventStream($this->getEventsArrayForAggregate($aggregateClass, $aggregateId), $aggregateClass, $aggregateId, $this->latestSequence);
    }

    public function appendEventsForAggregate($aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence)
    {
        if ($this->getAggregateVersion($aggregateClass, $aggregateId) != $expectedVersion) {
            throw new ConcurrentModificationException();
        }

        $this->addEventsToArrayForAggregate($aggregateId, $aggregateClass, $eventsWithMetaData);

        $this->versions[$this->constructKey($aggregateClass, $aggregateId)] = $expectedVersion + 1;
        $this->latestSequence = $expectedSequence + 1;
    }

    public function appendEventsForAggregateWithoutChecking($aggregateId, $aggregateClass, $newEvents)
    {
        $this->addEventsToArrayForAggregate($aggregateId, $aggregateClass, $this->decorateEventsWithMetadata($aggregateClass, $aggregateId, $newEvents));

        $constructKey = $this->constructKey($aggregateClass, $aggregateId);

        if (!isset($this->versions[$constructKey])) {
            $this->versions[$constructKey] = 0;
        }

        $this->versions[$constructKey]++;
        $this->latestSequence++;
    }

    private function getEventsArrayForAggregate(string $aggregateClass, $aggregateId)
    {
        $aggregateKey = $this->constructKey($aggregateClass, $aggregateId);
        return isset($this->events[$aggregateKey]) ? $this->events[$aggregateKey] : [];
    }

    private function addEventsToArrayForAggregate($aggregateId, $aggregateClass, $newEvents)
    {
        foreach ($newEvents as $event) {
            $this->events[$this->constructKey($aggregateClass, $aggregateId)][] = $event;
        }
    }

    public function loadEventsByClassNames(array $eventClasses): EventStream
    {
        $result = [];

        foreach ($this->events as $events) {
            /** @var EventWithMetaData[] $events */
            foreach ($events as $eventWithMetaData) {
                if ($this->eventHasAnyOfThisClasses($eventWithMetaData->getEvent(), $eventClasses)) {
                    $result[] = $eventWithMetaData;
                }
            }
        }

        return new RawEventStream($result);
    }

    private function eventHasAnyOfThisClasses($event, array $eventClasses)
    {
        foreach ($eventClasses as $eventClass) {

            if ((new SubclassComparator())->isASubClassOrSameClass($event, $eventClass)) {
                return true;
            }
        }

        return false;
    }

    public function getAggregateVersion(string $aggregateClass, $aggregateId)
    {
        $key = $this->constructKey($aggregateClass, $aggregateId);

        return isset($this->versions[$key]) ? $this->versions[$key] : 0;
    }

    /**
     * @param $aggregateClass
     * @param $aggregateId
     * @param Event[] $priorEvents
     * @return EventWithMetaData[]
     */
    public function decorateEventsWithMetadata($aggregateClass, $aggregateId, array $priorEvents)
    {
        return array_map(function (Event $event) use ($aggregateClass, $aggregateId) {
            return new EventWithMetaData($event, new MetaData(
                $aggregateId, $aggregateClass, new \DateTimeImmutable(), null
            ));
        }, $priorEvents);
    }

    public function fetchLatestSequence(): int
    {
        return $this->latestSequence;
    }

    private function constructKey(string $aggregateClass, $aggregateId): string
    {
        return $aggregateClass . '_' . (string)$aggregateId;
    }
}