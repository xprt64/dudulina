<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel\ReadModelTail;


use Dudulina\Event\EventWithMetaData;

interface EventProcessedNotifier
{
    public function onEventProcessed(EventWithMetaData $eventWithMetaData): void;
}