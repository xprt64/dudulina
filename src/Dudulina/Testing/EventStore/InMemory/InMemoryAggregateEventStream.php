<?php
/**
 * Copyright (c) 2019 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Testing\EventStore\InMemory;


use Dudulina\EventStore\AggregateEventStream;

class InMemoryAggregateEventStream implements AggregateEventStream
{

    private array $eventsArray;
    private int $version;

    public function __construct(array $eventsArray, /**
     * @var
     */
    private $aggregateClass, /**
     * @var
     */
    private $aggregateId, private int $sequence)
    {
        $this->version = count($eventsArray);
        $this->eventsArray = $eventsArray;
    }

    public function getIterator():\Traversable
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

    public function withIncrementedVersion():self
    {
        $other = clone $this;
        $other->version++;
        return $other;
    }

    public function withIncrementedSequence():self
    {
        $other = clone $this;
        $other->sequence++;
        return $other;
    }

    public function count(): int
    {
        return \count($this->eventsArray);
    }
}
