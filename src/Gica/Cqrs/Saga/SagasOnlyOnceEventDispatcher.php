<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;

class SagasOnlyOnceEventDispatcher implements \Gica\Cqrs\Event\EventDispatcher
{

    /** @var EventSubscriber */
    private $eventSubscriber;
    /**
     * @var SagaRepository
     */
    private $sagaRepository;

    public function __construct(
        SagaRepository $sagaRepository,
        EventSubscriber $eventSubscriber
    )
    {
        $this->eventSubscriber = $eventSubscriber;
        $this->sagaRepository = $sagaRepository;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetaData)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetaData->getEvent());

        foreach ($listeners as $listener) {
            $metaData = $eventWithMetaData->getMetaData();

            if (is_array($listener)) {
                $saga = $listener[0];

                if (!$this->sagaRepository->isEventAlreadyDispatched(get_class($saga), $metaData->getSequence(), $metaData->getIndex())) {
                    call_user_func($listener, $eventWithMetaData->getEvent(), $metaData);

                    $this->sagaRepository->persistLastProcessedEventBySaga(get_class($saga), $metaData->getSequence(), $metaData->getIndex());
                }
            }
        }
    }
}