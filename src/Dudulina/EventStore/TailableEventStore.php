<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventStore;


interface TailableEventStore
{
    /**
     * @param callable $callback function(EventWithMetadata)
     * @param string[] $eventClasses
     * @param mixed|null $afterTimestamp
     */
    public function tail(callable $callback, $eventClasses = [], $afterTimestamp = null):void;
}