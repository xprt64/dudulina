<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Saga;


use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventSubscriber;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventProcessing\ConcurentEventProcessingException;
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

                $sagaId = get_class($saga);

                if (!$this->trackerRepository->isEventProcessingAlreadyStarted($sagaId, $metaData->getEventId())) {
                    try {
                        $this->trackerRepository->startProcessingEvent($sagaId, $metaData->getEventId());
                        call_user_func($listener, $eventWithMetadata->getEvent(), $metaData);
                        $this->trackerRepository->endProcessingEvent($sagaId, $metaData->getEventId());
                    } catch (ConcurentEventProcessingException $exception) {
                        continue;
                    } catch (\Throwable $exception) {
                        $this->logger->error($exception->getMessage(), [
                            'saga'  => get_class($saga),
                            'event' => [
                                'class' => get_class($eventWithMetadata->getEvent()),
                                'id'    => (string)$metaData->getEventId(),
                            ],
                            'file'  => $exception->getFile(),
                            'line'  => $exception->getLine(),
                        ]);
                    }
                }
            }
        }
    }
}