<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventDispatcher;


class DoubleEventDispatcher implements \Gica\Interfaces\Cqrs\Event\EventDispatcher
{
    /**
     * @var \Gica\Interfaces\Cqrs\Event\EventDispatcher[]
     */
    private $eventDispatchers;

    public function __construct(
        \Gica\Interfaces\Cqrs\Event\EventDispatcher ...$eventDispatchers
    )
    {
        $this->eventDispatchers = $eventDispatchers;
    }

    /**
     * @inheritdoc
     */
    public function dispatchEvents(array $eventsWithMetaData)
    {
        foreach ($this->eventDispatchers as $i => $eventDispatcher) {
            $eventDispatcher->dispatchEvents($eventsWithMetaData);
        }
    }
}