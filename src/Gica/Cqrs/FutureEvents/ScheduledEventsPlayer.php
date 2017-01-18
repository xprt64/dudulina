<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\FutureEvents;


class ScheduledEventsPlayer
{

    /**
     * @var \Gica\Cqrs\FutureEventsStore
     */
    private $futureEventsStore;
    /**
     * @var \Gica\Interfaces\Cqrs\Event\EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var \Gica\Cqrs\EventStore
     */
    private $eventStore;
    /**
     * @var \Gica\Cqrs\Command\ConcurrentProofFunctionCaller
     */
    private $concurrentProofFunctionCaller;

    public function __construct(
        \Gica\Cqrs\FutureEventsStore $futureEventsStore,
        \Gica\Interfaces\Cqrs\Event\EventDispatcher $eventDispatcher,
        \Gica\Cqrs\EventStore $eventStore,
        \Gica\Cqrs\Command\ConcurrentProofFunctionCaller $concurrentProofFunctionCaller
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
            $this->eventDispatcher->dispatchEvents([$scheduledEvent->getEventWithMetaData()]);

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

        $nextSequence = 1 + $this->eventStore->fetchLatestSequence();

        $this->eventStore->appendEventsForAggregate($metaData->getAggregateId(), $metaData->getAggregateClass(), [$eventWithMetaData], $version, $nextSequence);
    }
}