<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

interface EventDispatcher
{
    public function dispatchEvent(EventWithMetaData $eventWithMetaData);
}