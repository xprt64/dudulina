<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

use Gica\Cqrs\Event;

interface EventSubscriber
{
    /**
     * @param Event $event
     * @return callable[]
     */
    public function getListenersForEvent(\Gica\Cqrs\Event $event);
}