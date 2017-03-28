<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;

class EventWithMetaData
{

    private $event;
    /**
     * @var MetaData
     */
    private $metaData;

    public function __construct(
        $event,
        MetaData $metaData
    )
    {
        $this->event = $event;
        $this->metaData = $metaData;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getMetaData(): MetaData
    {
        return $this->metaData;
    }
}