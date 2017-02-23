<?php


namespace Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\AggregateEventStream;
use Gica\Cqrs\EventStore\EventsCommit;
use Gica\Cqrs\EventStore\EventStreamGroupedByCommit;
use Gica\Cqrs\EventStore\Exception\ConcurrentModificationException;
use Gica\Iterator\IteratorTransformer\IteratorExpander;

class InMemoryEventStore implements EventStore
{
    /** @var EventsCommit[] */
    public $commitsByAggregate = [];
    private $versions = [];
    private $latestSequence = 0;

    public function loadEventsForAggregate(string $aggregateClass, $aggregateId): AggregateEventStream
    {
        return new InMemoryAggregateEventStream(
            $this->getEventsArrayForAggregate($aggregateClass, $aggregateId), $aggregateClass, $aggregateId, $this->latestSequence);
    }

    /**
     * @inheritdoc
     */
    public function appendEventsForAggregate($aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence)
    {
        if ($this->getAggregateVersion($aggregateClass, $aggregateId) != $expectedVersion) {
            throw new ConcurrentModificationException();
        }

        $this->appendEventsForAggregateWithoutChecking($aggregateId, $aggregateClass, $eventsWithMetaData, $expectedVersion, $expectedSequence);
    }

    public function appendEventsForAggregateWithoutChecking($aggregateId, $aggregateClass, $newEvents, int $expectedVersion, int $expectedSequence)
    {
        $this->addEventsToArrayForAggregate(
            $aggregateId,
            $aggregateClass,
            $this->decorateEventsWithMetadata($aggregateClass, $aggregateId, $newEvents),
            $expectedVersion,
            $expectedSequence
        );

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

        return isset($this->commitsByAggregate[$aggregateKey])
            ? $this->extractEventsFromCommits($this->commitsByAggregate[$aggregateKey])
            : [];
    }

    private function addEventsToArrayForAggregate($aggregateId, $aggregateClass, $newEvents, int $expectedVersion, int $expectedSequence)
    {
        $this->commitsByAggregate[$this->constructKey($aggregateClass, $aggregateId)][] = new EventsCommit(
            $expectedSequence, $expectedVersion, $newEvents
        );
    }

    public function loadEventsByClassNames(array $eventClasses): EventStreamGroupedByCommit
    {
        $commits = iterator_to_array((new IteratorExpander(function ($aggregateCommits) {
            yield from $aggregateCommits;
        }))($this->commitsByAggregate));

        return new FilteredRawEventStreamGroupedByCommit($commits, $eventClasses);
    }

    private function extractEventsFromCommits(array $commits = [])
    {
        $eventsExtracter = new IteratorExpander(function (EventsCommit $commit) {
            yield from $commit->getEventsWithMetadata();
        });

        return iterator_to_array($eventsExtracter($commits));
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
        return array_map(function ($event) use ($aggregateClass, $aggregateId) {
            if ($event instanceof EventWithMetaData) {
                return $event;
            }

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