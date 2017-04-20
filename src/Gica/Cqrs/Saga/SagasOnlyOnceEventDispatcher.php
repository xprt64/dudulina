<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentEventProcessingException;
use Psr\Log\LoggerInterface;

class SagasOnlyOnceEventDispatcher implements EventDispatcher
{

    /** @var EventSubscriber */
    private $eventSubscriber;
    /**
     * @var SagaEventTrackerRepository
     */
    private $trackerRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SagaEventTrackerRepository $trackerRepository,
        EventSubscriber $eventSubscriber,
        LoggerInterface $logger
    )
    {
        $this->eventSubscriber = $eventSubscriber;
        $this->trackerRepository = $trackerRepository;
        $this->logger = $logger;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetadata)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetadata->getEvent());

        foreach ($listeners as $listener) {
            $metaData = $eventWithMetadata->getMetaData();

            if (is_array($listener)) {
                $saga = $listener[0];

                $sagaId = get_class($saga) . $metaData->getAggregateId();

                if (!$this->trackerRepository->isEventProcessingAlreadyStarted($sagaId, $metaData->getEventId())) {
                    try {
                        $this->trackerRepository->startProcessingEventBySaga($sagaId, $metaData->getEventId());
                        call_user_func($listener, $eventWithMetadata->getEvent(), $metaData);
                        $this->trackerRepository->endProcessingEventBySaga($sagaId, $metaData->getEventId());
                    } catch (ConcurentEventProcessingException $exception) {
                        continue;
                    } catch (\Throwable $exception) {
                        $this->logger->error(sprintf("Saga %s event %d/%d processing error:%s", $sagaId, $metaData->getSequence(), $metaData->getIndex(), $exception->getMessage()), $exception->getTrace());
                    }
                }
            }
        }
    }
}