<?php


namespace tests\Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\EventStore\InMemory\RawEventStream;


class RawEventStreamTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $events  = [1, 2];

        $sut = new RawEventStream($events);

        $this->assertEquals($events, iterator_to_array($sut->getIterator()));

        $sut = new RawEventStream(new \ArrayIterator($events));

        $this->assertEquals($events, iterator_to_array($sut->getIterator()));
    }
}
