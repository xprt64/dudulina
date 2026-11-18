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

    public function __construct(private SagaEventTrackerRepository $trackerRepository, private EventSubscriber $eventSubscriber, private ?LoggerInterface $logger)
    {
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetadata)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetadata->getEvent());

        foreach ($listeners as $listener) {
            $metaData = $eventWithMetadata->getMetaData();

            if (is_array($listener)) {
                $saga = $listener[0];

                $sagaId = $saga::class;

                if (!$this->trackerRepository->isEventProcessingAlreadyStarted($sagaId, $metaData->getEventId())) {
                    try {
                        $this->trackerRepository->startProcessingEvent($sagaId, $metaData->getEventId());
                        call_user_func($listener, $eventWithMetadata->getEvent(), $metaData);
                        $this->trackerRepository->endProcessingEvent($sagaId, $metaData->getEventId());
                    } catch (ConcurentEventProcessingException $exception) {
                        continue;
                    } catch (\Throwable $exception) {
                        if ($this->logger) {
                            $this->logger->error($exception->getMessage(), [
                                'saga'  => $saga::class,
                                'event' => [
                                    'class' => $eventWithMetadata->getEvent()::class,
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
}