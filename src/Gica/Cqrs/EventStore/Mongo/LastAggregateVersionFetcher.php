<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;


class LastAggregateVersionFetcher
{
    public function fetchLatestVersion(\MongoDB\Collection $collection, string $aggregateClass, \Gica\Types\Guid $aggregateId):int
    {
        $cursor = $collection->find(
            [
                'aggregateId' => new \MongoDB\BSON\ObjectID($aggregateId->__toString()),
                'aggregateClass' => $aggregateClass,
            ],
            [
                'sort'  => [
                    'version' => -1,
                ],
                'limit' => 1,
            ]
        );

        $documents = $cursor->toArray();
        if ($documents) {
            $last = array_pop($documents);
            $version = (int)$last['version'];
        } else {
            $version = 0;
        }

        return $version;
    }
}