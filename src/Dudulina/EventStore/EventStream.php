<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\EventStore;


use Dudulina\Event\EventWithMetaData;

interface EventStream extends \IteratorAggregate, \Countable
{
    /**
     * @return \Iterator|EventWithMetaData[]
     */
    public function getIterator();
}