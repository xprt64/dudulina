<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventDispatcher;


use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\Event\EventWithMetaData;

class CompositeEventDispatcher implements EventDispatcher
{
    /**
     * @var EventDispatcher[]
     */
    private $eventDispatchers;

    public function __construct(
        EventDispatcher ...$eventDispatchers
    )
    {
        $this->eventDispatchers = $eventDispatchers;
    }

    public function dispatchEvent(EventWithMetaData $eventWithMetaData)
    {
        foreach ($this->eventDispatchers as $eventDispatcher) {
            $eventDispatcher->dispatchEvent($eventWithMetaData);
        }
    }
}