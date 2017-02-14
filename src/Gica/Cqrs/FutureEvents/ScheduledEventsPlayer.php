<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\FutureEvents;


use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\FutureEventsStore;

class ScheduledEventsPlayer
{

    /**
     * @var FutureEventsStore
     */
    private $futureEventsStore;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var ConcurrentProofFunctionCaller
     */
    private $concurrentProofFunctionCaller;

    public function __construct(
        FutureEventsStore $futureEventsStore,
        EventDispatcher $eventDispatcher,
        EventStore $eventStore,
        ConcurrentProofFunctionCaller $concurrentProofFunctionCaller
    )
    {
        $this->futureEventsStore = $futureEventsStore;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventStore = $eventStore;
        $this->concurrentProofFunctionCaller = $concurrentProofFunctionCaller;
    }

    public function run()
    {
        $this->futureEventsStore->loadAndProcessScheduledEvents(function (ScheduledEvent $scheduledEvent) {

            $this->saveEventToStore($scheduledEvent);
            $this->eventDispatcher->dispatchEvent($scheduledEvent->getEventWithMetaData());

        });
    }

    private function saveEventToStore(ScheduledEvent $scheduledEvent)
    {
        $this->concurrentProofFunctionCaller->executeFunction(function () use ($scheduledEvent) {
            $metaData = $scheduledEvent->getEventWithMetaData()->getMetaData();
            $aggregateVersion = $this->eventStore->getAggregateVersion($metaData->getAggregateClass(), $metaData->getAggregateId());
            $this->trySaveEventToStore($scheduledEvent, $aggregateVersion);
        }, 9999);
    }

    private function trySaveEventToStore(ScheduledEvent $scheduledEvent, $version)
    {
        $eventWithMetaData = $scheduledEvent->getEventWithMetaData();
        $metaData = $eventWithMetaData->getMetaData();

        $expectedSequence = $this->eventStore->fetchLatestSequence();

        $this->eventStore->appendEventsForAggregate($metaData->getAggregateId(), $metaData->getAggregateClass(), [$eventWithMetaData], $version, $expectedSequence);
    }
}