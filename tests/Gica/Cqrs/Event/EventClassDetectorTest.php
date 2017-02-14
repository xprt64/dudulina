<?php


namespace tests\Gica\Cqrs\Event;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\CodeAnalysis\EventClassDetector;


class EventClassDetectorTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventClass = new \ReflectionClass(SomeEvent::class);
        $aggregateClass = new \ReflectionClass(SomeAggregate::class);

        $sut = new EventClassDetector();

        $this->assertTrue($sut->isMessageClass($eventClass));

        $this->assertTrue($sut->isMethodAccepted($aggregateClass->getMethods()[0]));

        $this->assertFalse($sut->isMessageClass(new \ReflectionClass(SomeNonEvent::class)));
    }
}

class SomeEvent implements Event
{

}

class SomeNonEvent
{

}

class SomeAggregate
{
    public function myMethod()
    {

    }
}