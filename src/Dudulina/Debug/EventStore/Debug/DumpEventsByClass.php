<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Debug\EventStore\Debug;


use Dudulina\EventStore;
use Psr\Log\LoggerInterface;

class DumpEventsByClass
{

    public function __construct(private EventStore $eventStore, private LoggerInterface $logger)
    {
    }

    public function dumpEvents(array $eventClasses)
    {
        $this->logger->info(print_r($eventClasses, 1));
        $this->logger->info("loading events...\n");
        /** @var \Dudulina\Event\EventWithMetaData[] $allEvents */
        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);
        $this->logger->info("dumping events...\n");

        foreach ($allEvents as $eventWithMetaData) {
            $this->logger->info("\n");
            $this->logger->info("Event: " . $eventWithMetaData->getEvent()::class . "\n");
            $this->logger->info("Aggregate: " . $eventWithMetaData->getMetaData()->getAggregateClass() . '#' . $eventWithMetaData->getMetaData()->getAggregateId() . "\n");
            $this->logger->info("Created: " . $eventWithMetaData->getMetaData()->getDateCreated()->format('c') . "\n");
        }
    }
}