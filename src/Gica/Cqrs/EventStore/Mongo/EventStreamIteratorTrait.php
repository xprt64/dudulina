<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Mongo;
use Gica\Types\Guid;

/**
 * @property \Gica\Cqrs\Event\EventSerializer $eventSerializer
 */
trait EventStreamIteratorTrait
{
    private function getIteratorThatExtractsEventsFromDocument($cursor):\Traversable
    {
        $expanderCallback = function ($document) {
            $metaData = $this->extractMetaDataFromDocument($document);

            foreach ($document['events'] as $eventSubDocument) {
                $event = $this->eventSerializer->deserializeEvent($eventSubDocument['eventClass'], $eventSubDocument['payload']);

                yield new \Gica\Cqrs\Event\EventWithMetaDataDefault($event, $metaData);
            }

        };

        $generator = new \Gica\Iterator\IteratorTransformer\IteratorExpander($expanderCallback);

        return $generator($cursor);
    }

    private function extractMetaDataFromDocument($document)
    {
        /** @var \MongoDB\BSON\UTCDateTime $createdAt */
        $createdAt = $document['createdAt'];
        $dateCreated = \DateTimeImmutable::createFromMutable($createdAt->toDateTime());

        return new \Gica\Cqrs\Event\MetaDataDefault(
            new \Gica\Types\Guid((string)$document['aggregateId']),
            $document['aggregateClass'],
            $dateCreated,
            $document['authenticatedUserId'] ? new Guid($document['authenticatedUserId']) : null);
    }
}