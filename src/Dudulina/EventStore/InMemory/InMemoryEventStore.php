<?php


namespace Dudulina\EventStore\InMemory;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\AggregateEventStream;
use Dudulina\EventStore\EventsCommit;
use Dudulina\EventStore\EventStreamGroupedByCommit;
use Dudulina\EventStore\Exception\ConcurrentModificationException;
use Gica\Iterator\IteratorTransformer\IteratorExpander;

class InMemoryEventStore implements EventStore
{
    /** @var EventsCommit[] */
    public $commitsByAggregate = [];
    private $versions = [];
    private $latestSequence = 0;

    public function loadEventsForAggregate(AggregateDescriptor $aggregateDescriptor): AggregateEventStream
    {
        return new InMemoryAggregateEventStream(
            $this->getEventsArrayForAggregate($aggregateDescriptor), $aggregateDescriptor->getAggregateClass(), $aggregateDescriptor->getAggregateId(), $this->latestSequence);
    }

    /**
     * @inheritdoc
     */
    public function appendEventsForAggregate(AggregateDescriptor $aggregateDescriptor, $eventsWithMetaData, AggregateEventStream $expectedEventStream):void
    {
        if ($this->getAggregateVersion($aggregateDescriptor) != $expectedEventStream->getVersion()) {
            throw new ConcurrentModificationException();
        }

        $this->appendEventsForAggregateWithoutChecking($aggregateDescriptor, $eventsWithMetaData, $expectedEventStream);
    }

    public function appendEventsForAggregateWithoutChecking(AggregateDescriptor $aggregateDescriptor, $newEvents, AggregateEventStream $expectedEventStream)
    {
        $this->addEventsToArrayForAggregate(
            $aggregateDescriptor,
            $this->decorateEventsWithMetadata($aggregateDescriptor, $newEvents),
            $expectedEventStream
        );

        $constructKey = $this->constructKey($aggregateDescriptor);

        if (!isset($this->versions[$constructKey])) {
            $this->versions[$constructKey] = 0;
        }

        $this->versions[$constructKey]++;
        $this->latestSequence++;
    }

    private function getEventsArrayForAggregate(AggregateDescriptor $aggregateDescriptor)
    {
        $aggregateKey = $this->constructKey($aggregateDescriptor);

        return isset($this->commitsByAggregate[$aggregateKey])
            ? $this->extractEventsFromCommits($this->commitsByAggregate[$aggregateKey])
            : [];
    }

    private function addEventsToArrayForAggregate(AggregateDescriptor $aggregateDescriptor, $newEvents, AggregateEventStream $expectedEventStream)
    {
        $this->commitsByAggregate[$this->constructKey($aggregateDescriptor)][] = new EventsCommit(
            $expectedEventStream->getSequence(), $expectedEventStream->getVersion(), $newEvents
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

    public function getAggregateVersion(AggregateDescriptor $aggregateDescriptor)
    {
        $key = $this->constructKey($aggregateDescriptor);

        return isset($this->versions[$key]) ? $this->versions[$key] : 0;
    }

    /**
     * @param AggregateDescriptor $aggregateDescriptor
     * @param array $priorEvents
     * @return EventWithMetaData[]
     */
    public function decorateEventsWithMetadata(AggregateDescriptor $aggregateDescriptor, array $priorEvents)
    {
        return array_map(function ($event) use ($aggregateDescriptor) {
            if ($event instanceof EventWithMetaData) {
                return $event;
            }

            return new EventWithMetaData($event, new MetaData(
                $aggregateDescriptor->getAggregateId(),
                $aggregateDescriptor->getAggregateClass(),
                    new \DateTimeImmutable(),
                    null
            ));
        }, $priorEvents);
    }

    public function fetchLatestSequence(): int
    {
        return $this->latestSequence;
    }

    private function constructKey(AggregateDescriptor $aggregateDescriptor): string
    {
        return $aggregateDescriptor->getAggregateClass() . '_' . (string)$aggregateDescriptor->getAggregateId();
    }

    public function findEventById(string $eventId): ?EventWithMetaData
    {
        foreach($this->commitsByAggregate as $commits)
        {
            foreach($commits as $commit)
            {
                /** @var \Dudulina\EventStore\EventsCommit $commit */

                foreach($commit->getEventsWithMetadata() as $eventWithMetadata)
                {
                    if($eventWithMetadata->getMetaData()->getEventId() === $eventId)
                    {
                        return $eventWithMetadata;
                    }
                }
            }

        }

        return null;
    }

    public function factoryAggregateEventStream(AggregateDescriptor $aggregateDescriptor)
    {
        return new InMemoryAggregateEventStream(
            $this->getEventsArrayForAggregate($aggregateDescriptor),
            $aggregateDescriptor->getAggregateId(),
            $aggregateDescriptor->getAggregateClass(),
            $this->latestSequence);
    }
}