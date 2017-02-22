<?php


namespace Gica\Cqrs\EventStore;


interface ByClassNamesEventStream extends EventStream
{
    public function limitCommits(int $limit);

    public function afterSequence(int $sequenceNumber);

    public function countCommits():int;

    /**
     * @return array|\ArrayIterator
     */
    public function fetchCommits();
}