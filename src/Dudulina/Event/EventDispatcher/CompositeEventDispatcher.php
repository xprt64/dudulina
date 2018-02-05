<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventDispatcher;


use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventWithMetaData;

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

    public function dispatchEvent(EventWithMetaData $eventWithMetadata)
    {
        foreach ($this->eventDispatchers as $eventDispatcher) {
            $eventDispatcher->dispatchEvent($eventWithMetadata);
        }
    }
}