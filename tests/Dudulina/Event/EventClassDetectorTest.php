<?php


namespace tests\Dudulina\Event;


use Dudulina\Event;
use Dudulina\CodeGeneration\Event\EventClassDetector;


class EventClassDetectorTest extends \PHPUnit\Framework\TestCase
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