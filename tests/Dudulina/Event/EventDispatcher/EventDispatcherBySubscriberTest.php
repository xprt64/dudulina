<?php


namespace tests\Dudulina\Event\EventDispatcher;


use Dudulina\Event;
use Dudulina\Event\EventDispatcher\ErrorReporter;
use Dudulina\Event\EventDispatcher\EventDispatcherBySubscriber;
use Dudulina\Event\EventSubscriber;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Throwable;


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
            $eventSubscriber,
            new class implements ErrorReporter{
                public function reportEventDispatchError(callable $listener, EventWithMetaData $eventWithMetadata, Throwable $exception):void
                {
                    //nothing to do
                }
            }
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

        $logger = $this->getMockBuilder(ErrorReporter::class)
            ->getMock();
        $logger->expects($this->once())
            ->method('reportEventDispatchError');
        /** @var ErrorReporter $logger */


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
