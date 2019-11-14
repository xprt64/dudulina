<?php
/**
 * Copyright (c) 2019 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Event\EventDispatcher;

use Dudulina\Event\EventWithMetaData;
use Throwable;

interface ErrorReporter
{
    /**
     * @param callable $listener
     * @param EventWithMetaData $eventWithMetadata
     * @param Throwable $exception
     */
    public function reportEventDispatchError(callable $listener, EventWithMetaData $eventWithMetadata, Throwable $exception):void;
}