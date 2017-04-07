<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga;

use Gica\Cqrs\Event\EventSubscriber;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Saga\SagaEventTrackerRepository;
use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentModificationException;
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

        $repository = $this->getMockBuilder(SagaEventTrackerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->method('beginProcessingEventBySaga')
            ->with(get_class($saga), 1, 2);

        $repository->method('endProcessingEventBySaga')
            ->with(get_class($saga), 1, 2);


        /** @var SagaEventTrackerRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber);

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

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturn(false);

        $repository->method('beginProcessingEventBySaga')
            ->with(get_class($saga), 1, 2)
            ->willThrowException(new ConcurentModificationException());

        $repository
            ->expects($this->never())
            ->method('endProcessingEventBySaga')
            ->with(get_class($saga), 1, 2);


        /** @var SagaEventTrackerRepository $repository */
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

        $repository = $this->getMockBuilder(SagaEventTrackerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturn(true);

        $repository->expects($this->never())
            ->method('beginProcessingEventBySaga');

        $repository->expects($this->never())
            ->method('endProcessingEventBySaga');

        /** @var SagaEventTrackerRepository $repository */
        /** @var EventSubscriber $subscriber */
        $sut = new SagasOnlyOnceEventDispatcher($repository, $subscriber);

        /** @var EventWithMetaData $eventWithMetadata */
        $sut->dispatchEvent($eventWithMetadata);
    }
}
