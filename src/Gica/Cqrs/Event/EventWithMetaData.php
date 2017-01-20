<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

use Gica\Cqrs\Event;

class EventWithMetaData
{

    /**
     * @var Event
     */
    private $event;
    /**
     * @var MetaData
     */
    private $metaData;

    public function __construct(
        Event $event,
        MetaData $metaData
    )
    {
        $this->event = $event;
        $this->metaData = $metaData;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return MetaData
     */
    public function getMetaData(): MetaData
    {
        return $this->metaData;
    }
}