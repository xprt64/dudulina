<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


class EventDispatcherBySubscriber implements \Gica\Cqrs\Event\EventDispatcher
{
    /** @var \Gica\Cqrs\Event\EventSubscriber */
    private $eventSubscriber;

    public function __construct(
        \Gica\Cqrs\Event\EventSubscriber $eventSubscriber
    )
    {
        $this->eventSubscriber = $eventSubscriber;
    }

    /**
     * @inheritdoc
     */
    public function dispatchEvents(array $eventsWithMetaData)
    {
        foreach ($eventsWithMetaData as $eventWithMetaData) {
            $this->dispatchEvent($eventWithMetaData);
        }
    }

    protected function dispatchEvent(\Gica\Cqrs\Event\EventWithMetaData $eventWithMetaData)
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