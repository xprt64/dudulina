<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;

//
//class MongoAggregateNewestEventStream implements \Gica\Cqrs\EventStore\EventStream
//{
//    use \Gica\Cqrs\EventStore\Mongo\EventStreamIteratorTrait;
//
//    /**
//     * @var \MongoDB\Collection
//     */
//    private $collection;
//    /**
//     * @var
//     */
//    private $aggregateId;
//    private $version;
//    /**
//     * @var \Gica\Cqrs\EventStore\Mongo\EventSerializer
//     */
//    private $eventSerializer;
//    /**
//     * @var int
//     */
//    private $fromVersion;
//
//    public function __construct(
//        \MongoDB\Collection $collection,
//        $aggregateId,
//        \Gica\Cqrs\EventStore\Mongo\EventSerializer $eventSerializer,
//        int $fromVersion
//    )
//    {
//        $this->collection = $collection;
//        $this->aggregateId = $aggregateId;
//        $this->eventSerializer = $eventSerializer;
//        $this->fromVersion = $fromVersion;
//        $this->version = $this->fetchLatestVersion($aggregateId);
//    }
//
//    /**
//     * @inheritdoc
//     */
//    public function getIterator()
//    {
//        $cursor = $this->getCursorBetweenVersions($this->aggregateId);
//
//        return $this->getIteratorThatExtractsEventsFromDocument($cursor);
//    }
//
//    public function getVersion():int
//    {
//        return $this->version;
//    }
//
//    private function fetchLatestVersion(\Gica\Types\Guid $aggregateId):int
//    {
//        $cursor = $this->collection->find(
//            [
//                'aggregateId' => new \MongoDB\BSON\ObjectID($aggregateId->__toString()),
//            ],
//            [
//                'sort'  => [
//                    'version' => -1,
//                ],
//                'limit' => 1,
//            ]
//        );
//
//        $documents = $cursor->toArray();
//        if ($documents) {
//            $last = array_pop($documents);
//            $version = (int)$last['version'];
//        } else {
//            $version = 0;
//        }
//
//        return $version;
//    }
//
//    private function getCursorBetweenVersions(\Gica\Types\Guid $aggregateId):\MongoDB\Driver\Cursor
//    {
//        $cursor = $this->collection->find(
//            [
//                'aggregateId' => new \MongoDB\BSON\ObjectID($aggregateId->__toString()),
//                'version'     => [
//                    '$lte' => $this->version,
//                    '$gt'  => $this->fromVersion,
//                ],
//            ],
//            [
//                'sort' => [
//                    'version' => 1,
//                ],
//            ]
//        );
//        return $cursor;
//    }
//}