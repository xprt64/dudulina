<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\EventStore;


interface AggregateEventStream extends EventStream
{
    public function getVersion(): int;

    public function getSequence(): int;
}