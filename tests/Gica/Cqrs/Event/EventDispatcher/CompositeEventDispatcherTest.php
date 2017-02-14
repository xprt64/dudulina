<?php


namespace tests\Gica\Cqrs\Event\EventDispatcher;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\Event\EventDispatcher\CompositeEventDispatcher;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;


class CompositeEventDispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventWithMetaData = new EventWithMetaData(
            $this->mockEvent(),
            $this->mockMetadata()
        );

        $firstEventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->getMock();
        $firstEventDispatcher->expects($this->once())
            ->method('dispatchEvent')
            ->with($this->equalTo($eventWithMetaData));

        $secondEventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->getMock();
        $secondEventDispatcher->expects($this->once())
            ->method('dispatchEvent')
            ->with($this->equalTo($eventWithMetaData));

        $sut = new CompositeEventDispatcher(
            $firstEventDispatcher, $secondEventDispatcher
        );
        $sut->dispatchEvent($eventWithMetaData);
    }

    private function mockEvent()
    {
        return $this->getMockBuilder(Event::class)
            ->getMock();
    }

    private function mockMetadata()
    {
        return $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
