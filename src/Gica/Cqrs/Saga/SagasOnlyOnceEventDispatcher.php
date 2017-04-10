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

    public function dispatchEvent(EventWithMetaData $eventWithMetaData)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetaData->getEvent());

        foreach ($listeners as $listener) {
            $metaData = $eventWithMetaData->getMetaData();

            if (is_array($listener)) {
                $saga = $listener[0];

                if (!$this->trackerRepository->isEventAlreadyDispatched(get_class($saga), $metaData->getSequence(), $metaData->getIndex())) {
                    try {
                        $this->trackerRepository->beginProcessingEventBySaga(get_class($saga), $metaData->getSequence(), $metaData->getIndex());
                        call_user_func($listener, $eventWithMetaData->getEvent(), $metaData);
                        $this->trackerRepository->endProcessingEventBySaga(get_class($saga), $metaData->getSequence(), $metaData->getIndex());
                    } catch (ConcurentEventProcessingException $exception) {
                        continue;
                    }
                }
            }
        }
    }
}