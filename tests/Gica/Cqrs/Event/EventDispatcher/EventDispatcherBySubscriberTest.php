<?php


namespace tests\Gica\Cqrs\Event\EventDispatcher;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher\EventDispatcherBySubscriber;
use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;


class EventDispatcherBySubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventWithMetaData = new EventWithMetaData(
            $this->mockEvent(),
            $this->mockMetadata()
        );

        $listener = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['onEvent1'])
            ->getMock();
        $listener->expects($this->once())
            ->method('onEvent1');


        $eventSubscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $eventSubscriber->expects($this->once())
            ->method('getListenersForEvent')
            ->willReturn([
                [$listener, 'onEvent1'],
            ]);

        /** @var  EventSubscriber $eventSubscriber */
        $sut = new EventDispatcherBySubscriber(
            $eventSubscriber
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
        return $this->getMockBuilder(Event\MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
