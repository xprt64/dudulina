<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event;

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

    public function withSequenceAndIndex(int $sequence, int $index): self
    {
        $other = clone $this;
        $other->metaData = $other->metaData->withSequenceAndIndex($sequence, $index);
        return $other;
    }
}