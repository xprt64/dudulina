<?php


namespace tests\Gica\Cqrs\EventStore\InMemory;


use Gica\Cqrs\EventStore\InMemory\RawEventStream;


class RawEventStreamTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $groupedEventsArray  = [ 100 => [1, 2],  200 => [3, 4],  300 => [5, 6]];

        $sut = new RawEventStream($groupedEventsArray);

        $this->assertEquals([1, 2, 3, 4, 5, 6], iterator_to_array($sut->getIterator()));

        $sut = new RawEventStream(new \ArrayIterator($groupedEventsArray));

        $this->assertEquals([1, 2, 3, 4, 5, 6], iterator_to_array($sut->getIterator()));


        $sut = new RawEventStream($groupedEventsArray);
        $sut->afterSequence(1);
        $this->assertEquals([3, 4, 5, 6], iterator_to_array($sut->getIterator()));

        $sut = new RawEventStream($groupedEventsArray);
        $sut->limitCommits(2);
        $this->assertEquals([1, 2, 3, 4], iterator_to_array($sut->getIterator()));

        $sut = new RawEventStream($groupedEventsArray);
        $sut->afterSequence(1);
        $sut->limitCommits(1);
        $this->assertEquals([3, 4], iterator_to_array($sut->getIterator()));

        $this->assertEquals(3, $sut->countCommits());
    }
}
