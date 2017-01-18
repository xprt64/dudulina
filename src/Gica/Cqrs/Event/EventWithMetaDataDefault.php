<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


class EventWithMetaDataDefault implements \Gica\Cqrs\Event\EventWithMetaData
{

    /**
     * @var \Gica\Cqrs\Event
     */
    private $event;
    /**
     * @var \Gica\Cqrs\Event\MetaData
     */
    private $metaData;

    public function __construct(
        \Gica\Cqrs\Event $event,
        \Gica\Cqrs\Event\MetaData $metaData
    )
    {
        $this->event = $event;
        $this->metaData = $metaData;
    }

    /**
     * @return \Gica\Cqrs\Event
     */
    public function getEvent(): \Gica\Cqrs\Event
    {
        return $this->event;
    }

    /**
     * @return \Gica\Cqrs\Event\MetaData
     */
    public function getMetaData(): \Gica\Cqrs\Event\MetaData
    {
        return $this->metaData;
    }
}