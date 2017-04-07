<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga;

use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Saga\SagaRepository;
use Gica\Cqrs\Saga\SagasOnlyOnceEventDispatcher;

class SagasOnlyOnceEventDispatcherTest extends \PHPUnit_Framework_TestCase
{

    const SAGA_ID = 'sagaId';

    public function test_dispatchEvent_not_isEventAlreadyDispatched()
    {
        $metadata = $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata->method('getSequence')
            ->willReturn(1);

        $metadata->method('getIndex')
            ->willReturn(2);

        /** @var MetaData $metadata */
        $eventWithMetadata = new EventWithMetaData('event', $metadata);

        $saga = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['someListenerMethod'])
            ->getMock();

        $saga->expects($this->once())
            ->method('someListenerMethod');

        $subscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $subscriber->method('getListenersForEvent')
            ->willReturn([[$saga, 'someListenerMethod']]);

        $repository = $this->getMockBuilder(SagaRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->method('persistLastProcessedEventBySaga')
            ->with(get_class($saga), 1, 2);


        /** @var SagaRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber);

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }

    public function test_dispatchEvent_will_not_save_if_exception()
    {
        $metadata = $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata->method('getSequence')
            ->willReturn(10);

        $metadata->method('getIndex')
            ->willReturn(20);

        /** @var MetaData $metadata */
        $eventWithMetadata = new EventWithMetaData('event', $metadata);

        $saga = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['someListenerMethod'])
            ->getMock();

        $saga->expects($this->once())
            ->method('someListenerMethod')
            ->willThrowException(new \Exception("Some random exception"));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Some random exception");

        $subscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $subscriber->method('getListenersForEvent')
            ->willReturn([[$saga, 'someListenerMethod']]);

        $repository = $this->getMockBuilder(SagaRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->expects($this->never())
            ->method('persistLastProcessedEventBySaga');

        /** @var SagaRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber);

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }

    public function test_dispatchEvent_already_parsed()
    {
        $metadata = $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata->method('getSequence')
            ->willReturn(1);

        $metadata->method('getIndex')
            ->willReturn(1);

        /** @var MetaData $metadata */
        $eventWithMetadata = new EventWithMetaData('event', $metadata);

        $saga = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['someListenerMethod'])
            ->getMock();

        $saga->expects($this->never())
            ->method('someListenerMethod');

        $subscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $subscriber->method('getListenersForEvent')
            ->willReturn([[$saga, 'someListenerMethod']]);

        $repository = $this->getMockBuilder(SagaRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturn(true);

        $repository->expects($this->never())
            ->method('persistLastProcessedEventBySaga');

        /** @var SagaRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber);

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }
}
