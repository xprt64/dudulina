<?php


namespace Dudulina\EventStore;


interface EventStreamGroupedByCommit extends EventStream
{
    public function limitCommits(int $limit);

    public function afterSequence(int $sequenceNumber);

    public function beforeSequence(int $sequenceNumber);

    public function countCommits():int;

    /**
     * @return EventsCommit[]|\ArrayIterator
     */
    public function fetchCommits();
}