<?php


namespace Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\EventStore\ByClassNamesEventStream;
use Gica\Iterator\IteratorTransformer\IteratorExpander;

class RawEventStream implements ByClassNamesEventStream
{

    private $groupedEventsArray = [];

    /** @var int|null */
    private $limit;

    /** @var  int|null */
    private $skip;

    public function __construct($groupedEventsArray)
    {
        $this->groupedEventsArray = $groupedEventsArray;
    }

    public function getIterator()
    {
        $groupedEvents = $this->fetchCommits();

        $deGrouper = new IteratorExpander(function ($group) {
            foreach ($group as $event) {
                yield $event;
            }
        });

        $events = iterator_to_array($deGrouper($groupedEvents));

        return new \ArrayIterator($events);
    }

    /**
     * @return array|\ArrayIterator
     */
    public function fetchCommits()
    {
        $groupedEvents = $this->groupedEventsArray;

        if ($this->groupedEventsArray instanceof \Iterator || $this->groupedEventsArray instanceof \IteratorAggregate) {
            $groupedEvents = iterator_to_array($this->groupedEventsArray);
        }

        if ($this->skip) {
            $groupedEvents = array_slice($groupedEvents, $this->skip);
        }

        if ($this->limit) {
            $groupedEvents = array_slice($groupedEvents, 0, $this->limit);
        }

        return $groupedEvents;
    }

    public function limitCommits(int $limit)
    {
        $this->limit = $limit;
    }

    public function skipCommits(int $numberOfCommits)
    {
        $this->skip = $numberOfCommits;
    }

    public function countCommits(): int
    {
        return count($this->groupedEventsArray);
    }
}