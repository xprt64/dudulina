<?php


namespace Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\EventStore\EventsCommit;
use Gica\Cqrs\EventStore\EventStreamGroupedByCommit;
use Gica\Iterator\IteratorTransformer\IteratorExpander;

class FilteredRawEventStreamGroupedByCommit implements EventStreamGroupedByCommit
{

    /**
     * @var EventsCommit[]
     */
    private $eventCommits = [];

    /** @var int|null */
    private $limit;

    /** @var  int|null */
    private $afterSequenceNumber;

    /** @var  int|null */
    private $beforeSequenceNumber;

    /**
     * @var array
     */
    private $eventClasses;

    /**
     * @param  EventsCommit[] $eventCommits
     * @param array $eventClasses
     */
    public function __construct($eventCommits, array $eventClasses = [])
    {
        $this->eventCommits = $eventCommits;
        $this->eventClasses = $eventClasses;
    }

    public function getIterator()
    {
        $commits = $this->fetchCommits();

        $deGrouper = new IteratorExpander(function (EventsCommit $group) {
            yield from $group->getEventsWithMetadata();
        });

        $events = iterator_to_array($deGrouper($commits));

        return new \ArrayIterator($events);
    }

    /**
     * @inheritdoc
     */
    public function fetchCommits()
    {
        $commits = $this->fetchCommitsWithoutLimit();

        if ($this->limit) {
            $commits = array_slice($commits, 0, $this->limit);
        }

        return $this->sortCommits($commits);
    }

    public function limitCommits(int $limit)
    {
        $this->limit = $limit;
    }

    public function afterSequence(int $sequenceNumber)
    {
        $this->afterSequenceNumber = $sequenceNumber;
    }

    public function countCommits(): int
    {
        return count($this->fetchCommitsWithoutLimit());
    }

    public function beforeSequence(int $sequenceNumber)
    {
        $this->beforeSequenceNumber = $sequenceNumber;
    }

    /**
     * @param EventsCommit[] $eventCommits
     * @return EventsCommit[]
     */
    private function sortCommits(array $eventCommits)
    {
        usort($eventCommits, function (EventsCommit $first, EventsCommit $second) {
            return $first->getSequence() <=> $second->getSequence();
        });

        return $eventCommits;
    }

    /**
     * @param EventsCommit[] $eventCommits
     * @return EventsCommit[]
     */
    private function filterCommits($eventCommits): array
    {
        if ($this->afterSequenceNumber) {
            $eventCommits = array_filter($eventCommits, function (EventsCommit $commit) {
                return $commit->getSequence() > $this->afterSequenceNumber;
            });
        }

        if ($this->beforeSequenceNumber) {
            $eventCommits = array_filter($eventCommits, function (EventsCommit $commit) {
                return $commit->getSequence() < $this->beforeSequenceNumber;
            });
        }

        if ($this->eventClasses) {
            $eventCommits = array_filter($eventCommits, function (EventsCommit $commit) {
                $commit = $commit->filterEventsByClass($this->eventClasses);
                return !empty($commit->getEventsWithMetadata());
            });
        }

        return $eventCommits;
    }

    /**
     * @return EventsCommit[]
     */
    private function fetchCommitsWithoutLimit()
    {
        return $this->filterCommits($this->eventCommits);
    }


}