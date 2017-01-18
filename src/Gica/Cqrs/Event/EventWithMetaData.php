<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

interface EventWithMetaData
{
    /**
     * @return \Gica\Cqrs\Event
     */
    public function getEvent() : \Gica\Cqrs\Event;

    /**
     * @return \Gica\Cqrs\Event\MetaData
     */
    public function getMetaData() : \Gica\Cqrs\Event\MetaData;
}