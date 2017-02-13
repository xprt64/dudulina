<?php


namespace Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\EventStore\AggregateEventStream;

class InMemoryAggregateEventStream implements AggregateEventStream
{

    /**
     * @var array
     */
    private $eventsArray;
    private $version;
    private $sequence;
    /**
     * @var
     */
    private $aggregateClass;
    /**
     * @var
     */
    private $aggregateId;

    public function __construct(array $eventsArray, $aggregateClass, $aggregateId, int $sequence)
    {
        $this->version = count($eventsArray);
        $this->aggregateClass = $aggregateClass;
        $this->aggregateId = $aggregateId;
        $this->eventsArray = $eventsArray;
        $this->sequence = $sequence;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->eventsArray);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }
}
