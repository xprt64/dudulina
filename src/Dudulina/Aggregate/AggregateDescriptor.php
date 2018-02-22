<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Dudulina\Aggregate;


class AggregateDescriptor
{

    private $aggregateId;
    /**
     * @var string
     */
    private $aggregateClass;

    public function __construct(
        $aggregateId,
        string $aggregateClass
    )
    {
        $this->aggregateId = $aggregateId;
        $this->aggregateClass = $aggregateClass;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    public function getAggregateClass(): string
    {
        return $this->aggregateClass;
    }
}