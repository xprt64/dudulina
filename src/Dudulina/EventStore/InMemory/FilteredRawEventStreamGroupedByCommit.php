<?php


namespace Dudulina\EventStore\InMemory;


use Dudulina\EventStore\EventStream;
use Dudulina\EventStore\SeekableEventStream;
use Gica\Iterator\IteratorTransformer\IteratorExpander;

class FilteredRawEventStreamGroupedByCommit implements EventStream, SeekableEventStream
{
    /**
     * @var InMemoryEventsCommit[]
     */
    private $eventCommits = [];

    /** @var int|null */
    private $limit;

    /**
     * @var array
     */
    private $eventClasses;

    private $afterTimestamp;

    /**
     * @param  InMemoryEventsCommit[] $eventCommits
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

        $deGrouper = new IteratorExpander(function (InMemoryEventsCommit $group) {
            yield from $group->getEventsWithMetadata();
        });

        $events = iterator_to_array($deGrouper($commits));

        return new \ArrayIterator($events);
    }

    /**
     * @return InMemoryEventsCommit[]
     */
    public function fetchCommits()
    {
        $commits = $this->fetchCommitsWithoutLimit();

        if ($this->limit > 0) {
            $commits = \array_slice($commits, 0, $this->limit);
        }

        return $this->sortCommits($commits);
    }

    public function limitCommits(int $limit)
    {
        $this->limit = $limit;
    }

    public function countCommits(): int
    {
        return \count($this->fetchCommitsWithoutLimit());
    }

    /**
     * @param InMemoryEventsCommit[] $eventCommits
     * @return InMemoryEventsCommit[]
     */
    private function sortCommits(array $eventCommits)
    {
        usort($eventCommits, function (InMemoryEventsCommit $first, InMemoryEventsCommit $second) {
            return $first->getSequence() <=> $second->getSequence();
        });

        return $eventCommits;
    }

    /**
     * @param InMemoryEventsCommit[] $eventCommits
     * @return InMemoryEventsCommit[]
     */
    private function filterCommits($eventCommits): array
    {
        if (!empty($this->eventClasses)) {
            $eventCommits = array_filter($eventCommits, function (InMemoryEventsCommit $commit) {
                $commit = $commit->filterEventsByClass($this->eventClasses);
                return !empty($commit->getEventsWithMetadata());
            });
        }

        return $eventCommits;
    }

    /**
     * @return InMemoryEventsCommit[]
     */
    private function fetchCommitsWithoutLimit()
    {
        return $this->filterCommits($this->eventCommits);
    }

    public function count()
    {
        $commits = $this->fetchCommits();

        $deGrouper = new IteratorExpander(function (InMemoryEventsCommit $group) {
            yield from $group->getEventsWithMetadata();
        });

        return count(iterator_to_array($deGrouper($commits), false));
    }

    public function afterTimestamp($after)
    {
        $this->afterTimestamp = $after;
    }
}