<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventDispatcher;


use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;

class EventDispatcherBySubscriber implements \Gica\Cqrs\Event\EventDispatcher
{
    /** @var EventSubscriber */
    private $eventSubscriber;

    public function __construct(
        EventSubscriber $eventSubscriber
    )
    {
        $this->eventSubscriber = $eventSubscriber;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetaData)
    {
        $listeners = $this->eventSubscriber->getListenersForEvent($eventWithMetaData->getEvent());

        if (!$listeners) {
            return;
        }

        foreach ($listeners as $listener) {
            $returnValue = call_user_func($listener, $eventWithMetaData->getEvent(), $eventWithMetaData->getMetaData());

            if (false === $returnValue) {
                break;
            }
        }
    }
}