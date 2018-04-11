<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventStore\InMemory;

use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore\SeekableEventStream;
use Gica\Iterator\IteratorTransformer\IteratorExpander;
use Gica\Iterator\IteratorTransformer\IteratorFilter;

class FilteredRawEventStreamGroupedByCommit implements SeekableEventStream
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

    /** @var EventSequence|null */
    private $afterSequence;
    /** @var EventSequence|null */
    private $beforeSequence;
    private $ascending = true;

    /**
     * @param  InMemoryEventsCommit[] $eventCommits
     * @param array $eventClasses
     */
    public function __construct($eventCommits, array $eventClasses = [])
    {
        $this->eventCommits = $eventCommits;
        $this->eventClasses = $eventClasses;
        $this->sort(true);
    }

    public function getIterator()
    {
        $commits = $this->fetchCommits();

        $deGrouper = new IteratorExpander(function (InMemoryEventsCommit $group) {
            yield from $group->getEventsWithMetadata();
        });

        $events = $deGrouper($commits);

        if ($this->afterSequence) {
            $filter = new IteratorFilter(function (EventWithMetaData $eventWithMetaData) {
                /** @var EventSequence $eventSequence */
                $eventSequence = $eventWithMetaData->getMetaData()->getSequence();
                return $eventSequence->isAfter($this->afterSequence);
            });
            $events = $filter($events);
        }

        if ($this->beforeSequence) {
            $filter = new IteratorFilter(function (EventWithMetaData $eventWithMetaData) {
                /** @var EventSequence $eventSequence */
                $eventSequence = $eventWithMetaData->getMetaData()->getSequence();
                return $eventSequence->isBefore($this->beforeSequence);
            });
            $events = $filter($events);
        }

        return new \ArrayIterator(iterator_to_array($events, false));
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
            $order = $first->getCommitSequence() <=> $second->getCommitSequence();
            return $this->ascending ? $order : -$order;
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
        return \count(\iterator_to_array($this->getIterator(), false));
    }

    public function afterSequence(string $after)
    {
        $this->afterSequence = EventSequence::fromString($after);
    }

    public function beforeSequence(string $before)
    {
        $this->beforeSequence = EventSequence::fromString($before);
    }

    public function sort(bool $chronological)
    {
        $this->ascending = $chronological;
    }
}