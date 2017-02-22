<?php


namespace Gica\Cqrs\EventStore;


interface ByClassNamesEventStream extends EventStream
{
    public function limitCommits(int $limit);

    public function skipCommits(int $numberOfCommits);

    public function countCommits():int;
}