<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;

use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Iterator\IteratorTransformer\IteratorExpander;
use Gica\Types\Guid;
use MongoDB\BSON\UTCDateTime;

/**
 * @property \Gica\Cqrs\EventStore\Mongo\EventSerializer $eventSerializer
 */
trait EventStreamIteratorTrait
{
    private function getIteratorThatExtractsEventsFromDocument($cursor):\Traversable
    {
        $expanderCallback = function ($document) {
            $metaData = $this->extractMetaDataFromDocument($document);

            foreach ($document['events'] as $eventSubDocument) {
                $event = $this->eventSerializer->deserializeEvent($eventSubDocument['eventClass'], $eventSubDocument['payload']);

                yield new EventWithMetaData($event, $metaData);
            }

        };

        $generator = new IteratorExpander($expanderCallback);

        return $generator($cursor);
    }

    private function extractMetaDataFromDocument($document)
    {
        /** @var UTCDateTime $createdAt */
        $createdAt = $document['createdAt'];
        $dateCreated = \DateTimeImmutable::createFromMutable($createdAt->toDateTime());

        return new MetaData(
            new Guid((string)$document['aggregateId']),
            $document['aggregateClass'],
            $dateCreated,
            $document['authenticatedUserId'] ? new Guid($document['authenticatedUserId']) : null);
    }
}