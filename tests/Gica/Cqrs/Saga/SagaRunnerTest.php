<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga;

use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\Saga\SagaRepository;
use Gica\Cqrs\Saga\SagaRunner;
use Psr\Log\LoggerInterface;

class SagaRunnerTest extends \PHPUnit_Framework_TestCase
{
    private function factoryMetadata(int $sequence, int $index)
    {
        return (new MetaData('', '', new \DateTimeImmutable('2017-01-01 00:00:00')))->withSequenceAndIndex($sequence, $index);
    }

    public function test()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            //->setMethods(['loadEventsByClassNames'])
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $eventStream = $this->getMockBuilder(\Gica\Cqrs\EventStore\EventStreamGroupedByCommit::class)->getMock();

        $eventStream->method('getIterator')
            ->willReturn(new \ArrayIterator([
                new EventWithMetaData(new Event1(), $this->factoryMetadata(3, 33)),
                new EventWithMetaData(new Event2(), $this->factoryMetadata(4, 44)),
            ]));

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->willReturn($eventStream);

        $saga = new MySaga();

        $repository = $this->getMockBuilder(SagaRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('isEventAlreadyDispatched')
            ->with(get_class($saga))
            ->willReturnCallback(function (string $sagaId, int $sequence, int $index) {
                return $sequence == 3;
            });

        $repository->expects($this->once())
            ->method('persistLastProcessedEventBySaga');

        /** @var SagaRepository $repository */
        /** @var LoggerInterface $logger */
        /** @var EventStore $eventStore */

        $sut = new SagaRunner(
            $eventStore,
            $logger,
            $repository
        );

        $sut->feedSagaWithEvents($saga);

        $this->assertSame(0, $saga->event1Called);
        $this->assertSame(1, $saga->event2Called);
    }


    public function test_afterSequence()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            //->setMethods(['loadEventsByClassNames'])
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $eventStream = $this->getMockBuilder(\Gica\Cqrs\EventStore\EventStreamGroupedByCommit::class)->getMock();

        $eventStream
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
            ]));

        $eventStream
            ->expects($this->once())
            ->method('afterSequence')
            ->with(4)
            ;

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->willReturn($eventStream);

        $saga = new MySaga();

        $repository = $this->getMockBuilder(SagaRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->never())
            ->method('isEventAlreadyDispatched');

        /** @var SagaRepository $repository */
        /** @var LoggerInterface $logger */
        /** @var EventStore $eventStore */

        $sut = new SagaRunner(
            $eventStore,
            $logger,
            $repository
        );

        $sut->feedSagaWithEvents($saga, 4);

        $this->assertSame(0, $saga->event1Called);
        $this->assertSame(0, $saga->event2Called);
    }
}

class MySaga
{
    public $event1Called = 0;
    public $event2Called = 0;

    public function processEvent1(Event1 $event)
    {
        $this->event1Called++;
    }

    public function processEvent2(Event2 $event)
    {
        $this->event2Called++;
    }
}

class Event1 implements Event
{

}


class Event2 implements Event
{

}