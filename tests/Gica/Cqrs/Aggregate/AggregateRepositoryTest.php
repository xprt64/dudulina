<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/


namespace tests\Gica\Cqrs\Aggregate;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\AggregateEventStream;


class AggregateRepositoryTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = '123';


    const AGGREGATE_VERSION = 1;

    const EVENTS_SEQUENCE = 2;

    /** @var AggregateEventStream */
    private $aggregateEventStream;

    public function testLoadAndSaveAggregate()
    {
        $eventStore = $this->mockEventStore();
        $eventsApplier = $this->mockEventsApplierOnAggregate();


        $aggregateRepository = new AggregateRepository(
            $eventStore,
            $eventsApplier
        );

        $aggregate = $aggregateRepository->loadAggregate(Aggregate::class, self::AGGREGATE_ID);

        $this->assertInstanceOf(Aggregate::class, $aggregate);

        $aggregateRepository->saveAggregate(self::AGGREGATE_ID, $aggregate, $this->getNewEvents());
    }

    private function mockEventStore(): EventStore
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsForAggregate')
            ->with($this->equalTo(Aggregate::class), $this->equalTo(self::AGGREGATE_ID))
            ->willReturn($this->mockEventStream());


        $eventStore
            ->expects($this->once())
            ->method('appendEventsForAggregate')
            ->with(
                $this->equalTo(self::AGGREGATE_ID),
                $this->equalTo(Aggregate::class),
                $this->equalTo($this->getNewEvents()),
                $this->equalTo(self::AGGREGATE_VERSION),
                $this->equalTo(self::EVENTS_SEQUENCE));

        /** @var EventStore $eventStore */
        return $eventStore;
    }

    private function mockEventsApplierOnAggregate(): EventsApplierOnAggregate
    {
        $eventsApplier = $this->getMockBuilder(EventsApplierOnAggregate::class)
            ->getMock();

        $eventsApplier
            ->expects($this->once())
            ->method('applyEventsOnAggregate')
            ->with(
                $this->isInstanceOf(Aggregate::class),
                $this->isInstanceOf(AggregateEventStream::class));

        /** @var EventsApplierOnAggregate $eventsApplier */
        return $eventsApplier;
    }

    private function mockEventStream(): AggregateEventStream
    {
        if (!$this->aggregateEventStream) {


            $this->aggregateEventStream = $this->getMockBuilder(AggregateEventStream::class)
                ->getMock();

            $this->aggregateEventStream
                ->expects($this->any())
                ->method('getVersion')
                ->will($this->returnValue(self::AGGREGATE_VERSION));

            $this->aggregateEventStream
                ->expects($this->any())
                ->method('getSequence')
                ->will($this->returnValue(self::EVENTS_SEQUENCE));
        }

        return $this->aggregateEventStream;

    }

    private function getNewEvents(): array
    {
        return [1, 2];
    }
}

class Aggregate
{

}


