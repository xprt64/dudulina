<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;


class LastAggregateSequenceFetcher
{
    public function fetchLatestSequence(\MongoDB\Collection $collection):int
    {
        $cursor = $collection->find(
            [
            ],
            [
                'sort'  => [
                    'sequence' => -1,
                ],
                'limit' => 1,
            ]
        );

        $documents = $cursor->toArray();
        if ($documents) {
            $last = array_pop($documents);
            $version = (int)$last['sequence'];
        } else {
            $version = 0;
        }

        return $version;
    }
}