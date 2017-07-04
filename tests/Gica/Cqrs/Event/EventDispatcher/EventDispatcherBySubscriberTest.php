<?php


namespace tests\Gica\Cqrs\Event\EventDispatcher;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher\EventDispatcherBySubscriber;
use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Psr\Log\LoggerInterface;


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

    public function test_exception_is_catched()
    {
        $eventWithMetaData = new EventWithMetaData(
            $this->mockEvent(),
            $this->mockMetadata()
        );

        $listener = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['onEvent1'])
            ->getMock();
        $listener->expects($this->once())
            ->willThrowException(new \Exception("test exception"))
            ->method('onEvent1');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $logger->expects($this->once())
            ->method('error');


        $eventSubscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $eventSubscriber->expects($this->once())
            ->method('getListenersForEvent')
            ->willReturn([
                [$listener, 'onEvent1'],
            ]);

        /** @var  EventSubscriber $eventSubscriber */
        $sut = new EventDispatcherBySubscriber(
            $eventSubscriber,
            $logger
        );

        $sut->dispatchEvent($eventWithMetaData);
    }

    private function mockEvent()
    {
        return $this->getMockBuilder(Event::class)
            ->getMock();
    }

    /**
     * @return MetaData
     */
    private function mockMetadata()
    {
        $metadata = $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MetaData $metadata */
        return $metadata;
    }
}
