<?php
/**
 * Copyright (c) 2019 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Testing\EventStore\InMemory;


use Dudulina\EventStore\EventSequence;

class InMemoryEventSequence implements EventSequence
{

    /**
     * @var int
     */
    private $indexInsideCommit;
    /**
     * @var int
     */
    private $commitSequence;

    public function __construct(
        int $commitSequence,
        int $indexInsideCommit
    ) {
        $this->indexInsideCommit = $indexInsideCommit;
        $this->commitSequence = $commitSequence;
    }

    public function isBefore(EventSequence $other): bool
    {
        return $this->commitSequence < $other->commitSequence || ($this->commitSequence === $other->commitSequence && $this->indexInsideCommit < $other->indexInsideCommit);
    }

    public function isAfter(self $other): bool
    {
        return $this->commitSequence > $other->commitSequence || ($this->commitSequence === $other->commitSequence && $this->indexInsideCommit > $other->indexInsideCommit);
    }
}