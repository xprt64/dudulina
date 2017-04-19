<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentEventProcessingException;

class SagasOnlyOnceEventDispatcher implements \Gica\Cqrs\Event\EventDispatcher
{

    /** @var EventSubscriber */
    private $eventSubscriber;
    /**
     * @var SagaEventTrackerRepository
     */
    private $trackerRepository;

    public function __construct(
        SagaEventTrackerRepository $trackerRepository,
        EventSubscriber $eventSubscriber
    )
    {
        $this->eventSubscriber = $eventSubscriber;
        $this->trackerRepository = $trackerRepository;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetadata)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetadata->getEvent());

        foreach ($listeners as $listener) {
            $metaData = $eventWithMetadata->getMetaData();

            $eventOrder = new EventOrder($metaData->getSequence(), $metaData->getIndex());

            if (is_array($listener)) {
                $saga = $listener[0];

                if (!$this->trackerRepository->isEventProcessingAlreadyStarted(get_class($saga), $eventOrder)) {
                    try {
                        $this->trackerRepository->startProcessingEventBySaga(get_class($saga), $eventOrder);
                        call_user_func($listener, $eventWithMetadata->getEvent(), $metaData);
                        $this->trackerRepository->endProcessingEventBySaga(get_class($saga), $eventOrder);
                    } catch (ConcurentEventProcessingException $exception) {
                        continue;
                    }
                }
            }
        }
    }
}