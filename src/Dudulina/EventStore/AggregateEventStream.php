<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\EventStore;


interface AggregateEventStream extends EventStream
{
    public function getVersion(): int;
}