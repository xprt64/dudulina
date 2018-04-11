<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\EventStore\InMemory;

class EventSequenceTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Dudulina\EventStore\InMemory\EventSequence::fromString('xxx');
    }
}
