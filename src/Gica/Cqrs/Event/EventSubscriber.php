<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

interface EventSubscriber
{
    /**
     * @param $event
     * @return callable[]
     */
    public function getListenersForEvent($event);
}