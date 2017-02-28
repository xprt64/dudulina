<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Debug;


use Gica\Cqrs\EventStore;
use Psr\Log\LoggerInterface;

class DumpEventsByClass
{

    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EventStore $eventStore,
        LoggerInterface $logger
    )
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
    }

    public function dumpEvents(array $eventClasses)
    {
        $this->logger->info(print_r($eventClasses, 1));
        $this->logger->info("loading events...\n");
        /** @var \Gica\Cqrs\Event\EventWithMetaData[] $allEvents */
        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);
        $this->logger->info("dumping events...\n");

        foreach ($allEvents as $eventWithMetaData) {
            $this->logger->info("\n");
            $this->logger->info("Event: " . get_class($eventWithMetaData->getEvent()) . "\n");
            $this->logger->info("Aggregate: " . $eventWithMetaData->getMetaData()->getAggregateClass() . '#' . $eventWithMetaData->getMetaData()->getAggregateId() . "\n");
            $this->logger->info("Created: " . $eventWithMetaData->getMetaData()->getDateCreated()->format('c') . "\n");
        }
    }
}