<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga;

use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Saga\SagaEventTrackerRepository;
use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentEventProcessingException;
use Gica\Cqrs\Saga\SagasOnlyOnceEventDispatcher;
use Psr\Log\LoggerInterface;

class SagasOnlyOnceEventDispatcherTest extends \PHPUnit_Framework_TestCase
{

    const SAGA_ID = 'sagaId';

    public function test_dispatchEvent_not_isEventProcessingAlreadyStarted()
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

        $repository = $this->getMockBuilder(SagaEventTrackerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventProcessingAlreadyStarted')
            ->willReturn(false);

        $repository->method('startProcessingEventBySaga');

        $repository->method('endProcessingEventBySaga');


        /** @var SagaEventTrackerRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber, $this->factoryLogger());

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }

    public function test_dispatchEvent_ConcurentModificationException()
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

        $saga->expects($this->never())
            ->method('someListenerMethod');

        $subscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $subscriber->method('getListenersForEvent')
            ->willReturn([[$saga, 'someListenerMethod']]);

        $repository = $this->getMockBuilder(SagaEventTrackerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventProcessingAlreadyStarted')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->method('startProcessingEventBySaga')
            ->willThrowException(new ConcurentEventProcessingException());

        $repository
            ->expects($this->never())
            ->method('endProcessingEventBySaga');


        /** @var SagaEventTrackerRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber, $this->factoryLogger());

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

        $repository = $this->getMockBuilder(SagaEventTrackerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventProcessingAlreadyStarted')
            ->with(get_class($saga))
            ->willReturn(true);

        $repository->method('isEventProcessingAlreadyEnded')
            ->with(get_class($saga))
            ->willReturn(true);

        $repository->expects($this->never())
            ->method('startProcessingEventBySaga');

        $repository->expects($this->never())
            ->method('endProcessingEventBySaga');

        /** @var SagaEventTrackerRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber, $this->factoryLogger());

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }

    public function test_dispatchEvent_with_error_in_saga()
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

        $saga->expects($this->once())
            ->method('someListenerMethod')
            ->willThrowException(new \Exception("some message"));

        $subscriber = $this->getMockBuilder(EventSubscriber::class)
            ->getMock();

        $subscriber->method('getListenersForEvent')
            ->willReturn([[$saga, 'someListenerMethod']]);

        $repository = $this->getMockBuilder(SagaEventTrackerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventProcessingAlreadyStarted')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->method('isEventProcessingAlreadyEnded')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->expects($this->once())
            ->method('startProcessingEventBySaga');

        $repository->expects($this->never())
            ->method('endProcessingEventBySaga');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $logger->expects($this->once())
            ->method('error');

        /** @var SagaEventTrackerRepository $repository */
        /** @var EventSubscriber $subscriber */
        /** @var LoggerInterface $logger */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber, $logger);

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }

    private function factoryLogger()
    {
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }
}
