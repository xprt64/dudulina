<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event;

interface EventDispatcher
{
    public function dispatchEvent(EventWithMetaData $eventWithMetadata);
}