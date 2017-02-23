<?php


namespace Gica\Cqrs\EventStore;


interface EventStreamGroupedByCommit extends EventStream
{
    public function limitCommits(int $limit);

    public function afterSequenceAndAscending(int $sequenceNumber);

    public function beforeSequenceAndDescending(int $sequenceNumber);

    public function countCommits():int;

    /**
     * @return EventsCommit[]|\ArrayIterator
     */
    public function fetchCommits();
}