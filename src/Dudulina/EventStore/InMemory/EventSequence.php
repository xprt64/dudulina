<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventStore\InMemory;


class EventSequence
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
    )
    {
        $this->indexInsideCommit = $indexInsideCommit;
        $this->commitSequence = $commitSequence;
    }

    public function __toString()
    {
        return $this->commitSequence . ';' . $this->indexInsideCommit;
    }

    public static function fromString(string $str): self
    {
        if (!self::isValidString($str)) {
            throw new \InvalidArgumentException("Not a valid sequence (int,int): $str");
        }
        list($timestampStr, $indexStr) = explode(';', $str);
        return new static((int)$timestampStr, (int)$indexStr);
    }

    public static function isValidString(string $str): bool
    {
        return preg_match('#[\d+,\d+]#ims', $str);
    }

    public function isBefore(self $other): bool
    {
        return $this->commitSequence < $other->commitSequence || ($this->commitSequence === $other->commitSequence && $this->indexInsideCommit < $other->indexInsideCommit);
    }

    public function isAfter(self $other): bool
    {
        return $this->commitSequence > $other->commitSequence || ($this->commitSequence === $other->commitSequence && $this->indexInsideCommit > $other->indexInsideCommit);
    }
}