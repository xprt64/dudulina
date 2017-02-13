<?php


namespace Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\EventStore\EventStream;

class RawEventStream implements EventStream
{

    private $eventsArray = [];

    public function __construct($eventsArray)
    {
        $this->eventsArray = $eventsArray;
    }

    public function getIterator()
    {
        if ($this->eventsArray instanceof \Iterator || $this->eventsArray instanceof \IteratorAggregate) {
            return new \ArrayIterator(iterator_to_array($this->eventsArray));
        }

        return new \ArrayIterator($this->eventsArray);
    }
}