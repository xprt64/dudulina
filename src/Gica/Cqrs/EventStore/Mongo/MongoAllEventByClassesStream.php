<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;


use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Iterator\IteratorTransformer\IteratorExpander;

class MongoAllEventByClassesStream implements \Gica\Cqrs\EventStore\EventStream
{
    use \Gica\Cqrs\EventStore\Mongo\EventStreamIteratorTrait;

    /**
     * @var \MongoDB\Collection
     */
    private $collection;
    /**
     * @var \Gica\Cqrs\EventStore\Mongo\EventSerializer
     */
    private $eventSerializer;
    /**
     * @var array
     */
    private $eventClassNames;

    public function __construct(
        \MongoDB\Collection $collection,
        array $eventClassNames,
        \Gica\Cqrs\EventStore\Mongo\EventSerializer $eventSerializer
    )
    {
        $this->collection = $collection;
        $this->eventSerializer = $eventSerializer;
        $this->eventClassNames = $eventClassNames;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        $cursor = $this->getCursor();

        return $this->getIteratorThatExtractsInterestingEventsFromDocument($cursor);
    }

    private function getCursor():\MongoDB\Driver\Cursor
    {
        $cursor = $this->collection->find(
            [
                MongoEventStore::EVENTS_EVENT_CLASS => [
                    '$in' => $this->eventClassNames,
                ],
            ],
            [
                'sort' => [
                    'sequence' => 1,
                ],
            ]
        );
        return $cursor;
    }

    private function getIteratorThatExtractsInterestingEventsFromDocument($cursor):\Traversable
    {
        $expanderCallback = function ($document) {
            $metaData = $this->extractMetaDataFromDocument($document);

            foreach ($document['events'] as $eventSubDocument) {
                if (!$this->isInterestingEvent($eventSubDocument[MongoEventStore::EVENT_CLASS])) {
                    continue;
                }

                $event = $this->eventSerializer->deserializeEvent($eventSubDocument[MongoEventStore::EVENT_CLASS], $eventSubDocument['payload']);

                yield new EventWithMetaData($event, $metaData);
            }
        };

        $generator = new IteratorExpander($expanderCallback);

        return $generator($cursor);
    }

    private function isInterestingEvent($eventClass)
    {
        return in_array($eventClass, $this->eventClassNames);
    }

}