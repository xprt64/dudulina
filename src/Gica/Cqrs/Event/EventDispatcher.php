<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

interface EventDispatcher
{
    /**
     * @param \Gica\Cqrs\Event\EventWithMetaData[] $eventsWithMetaData
     */
    public function dispatchEvents(array $eventsWithMetaData);
}