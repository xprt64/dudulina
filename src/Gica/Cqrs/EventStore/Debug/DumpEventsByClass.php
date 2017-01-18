<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore\Debug;


use Gica\Cqrs\Event\EventWithMetaData;

class DumpEventsByClass
{

    /**
     * @var \Gica\Cqrs\EventStore
     */
    private $eventStore;
    /**
     * @var \Gica\Cqrs\Event\EventsApplierOnListener
     */
    private $eventsApplierOnListener;

    public function __construct(
        \Gica\Cqrs\EventStore $eventStore,
        \Gica\Cqrs\Event\EventsApplierOnListener $eventsApplierOnListener
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplierOnListener = $eventsApplierOnListener;
    }

    public function dumpEvents(array $eventClasses)
    {
        print_r($eventClasses);
        echo "loading events...\n";
        /** @var \Gica\Cqrs\Event\EventWithMetaData[] $allEvents */
        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);
        echo "dumping events...\n";

        foreach($allEvents as $eventWithMetaData)
        {
            echo "\n";
            echo "Event: " . get_class($eventWithMetaData->getEvent()) . "\n";
            echo "Aggregate: " . $eventWithMetaData->getMetaData()->getAggregateClass() . '#' . $eventWithMetaData->getMetaData()->getAggregateId() . "\n";
            echo "Created: " . $eventWithMetaData->getMetaData()->getDateCreated()->format('c') . "\n";
        }
    }
}