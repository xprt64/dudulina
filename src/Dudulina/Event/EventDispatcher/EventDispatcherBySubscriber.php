<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventDispatcher;


use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventSubscriber;
use Dudulina\Event\EventWithMetaData;

class EventDispatcherBySubscriber implements EventDispatcher
{
    /** @var EventSubscriber */
    private $eventSubscriber;
    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    public function __construct(
        EventSubscriber $eventSubscriber,
        ErrorReporter $errorReporter
    ) {
        $this->eventSubscriber = $eventSubscriber;
        $this->errorReporter = $errorReporter;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetadata)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetadata->getEvent());

        foreach ($listeners as $listener) {
            try {
                \call_user_func($listener, $eventWithMetadata->getEvent(), $eventWithMetadata->getMetaData());
            } catch (\Throwable $exception) {
                $this->errorReporter->reportEventDispatchError(
                    $listener,
                    $eventWithMetadata,
                    $exception
                );
            }
        }
    }
}