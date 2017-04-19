<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


class EventOrder
{
    /**
     * @var int
     */
    private $sequence;
    /**
     * @var int
     */
    private $index;

    public function __construct(
        int $sequence,
        int $index
    )
    {
        $this->sequence = $sequence;
        $this->index = $index;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}