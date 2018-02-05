<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventDispatcher;


use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventSubscriber;
use Dudulina\Event\EventWithMetaData;
use Psr\Log\LoggerInterface;

class EventDispatcherBySubscriber implements EventDispatcher
{
    /** @var EventSubscriber */
    private $eventSubscriber;

    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(
        EventSubscriber $eventSubscriber,
        LoggerInterface $logger = null
    )
    {
        $this->eventSubscriber = $eventSubscriber;
        $this->logger = $logger;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetadata)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetadata->getEvent());

        foreach ($listeners as $listener) {
            try {
                call_user_func($listener, $eventWithMetadata->getEvent(), $eventWithMetadata->getMetaData());
            } catch (\Throwable $exception) {
                if ($this->logger) {
                    $this->logger->error(
                        sprintf(
                            "Dispatch event of type %s to %s failed: %s",
                            get_class($eventWithMetadata->getEvent()),
                            get_class($listener[0]),
                            $exception->getMessage()),
                        $exception->getTrace());
                }
            }
        }
    }
}