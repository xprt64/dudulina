<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Scheduling;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Dudulina\Event\EventDispatcher;
use Dudulina\FutureEventsStore;

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
     * @var ConcurrentProofFunctionCaller
     */
    private $concurrentProofFunctionCaller;
    /**
     * @var AggregateRepository
     */
    private $aggregateRepository;

    public function __construct(
        FutureEventsStore $futureEventsStore,
        EventDispatcher $eventDispatcher,
        ConcurrentProofFunctionCaller $functionCaller,
        AggregateRepository $aggregateRepository
    )
    {
        $this->futureEventsStore = $futureEventsStore;
        $this->eventDispatcher = $eventDispatcher;
        $this->concurrentProofFunctionCaller = $functionCaller;
        $this->aggregateRepository = $aggregateRepository;
    }

    public function run()
    {
        $this->futureEventsStore->loadAndProcessScheduledEvents(function (ScheduledEventWithMetadata $scheduledEvent) {
            $this->saveEventToStore($scheduledEvent);
            $this->eventDispatcher->dispatchEvent($scheduledEvent->getEventWithMetaData());
        });
    }

    private function saveEventToStore(ScheduledEventWithMetadata $scheduledEvent)
    {
        $this->concurrentProofFunctionCaller->executeFunction(function () use ($scheduledEvent) {
            $this->trySaveEventToStore($scheduledEvent);
        }, 9999);
    }

    private function trySaveEventToStore(ScheduledEventWithMetadata $scheduledEvent)
    {
        $eventWithMetaData = $scheduledEvent->getEventWithMetaData();
        $metaData = $eventWithMetaData->getMetaData();

        $this->aggregateRepository->saveAggregate(
            $metaData->getAggregateId(),
            $this->aggregateRepository->loadAggregate(
                new AggregateDescriptor($metaData->getAggregateId(), $metaData->getAggregateClass())
            ),
            [$eventWithMetaData]
        );
    }
}