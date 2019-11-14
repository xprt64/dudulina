<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/


namespace tests\Dudulina\Aggregate;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\EventSourcedAggregateRepository;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\AggregateEventStream;
use Dudulina\Testing\EventStore\InMemory\InMemoryAggregateEventStream;


class AggregateRepositoryTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = '123';


    const AGGREGATE_VERSION = 1;

    const EVENTS_SEQUENCE = 2;

    /** @var AggregateEventStream */
    private $aggregateEventStream;

    private $firstEventWithMetadata;
    private $secondEventWithMetadata;

    protected function setUp()
    {
        $this->firstEventWithMetadata = new EventWithMetaData(1, new MetaData('', '', new \DateTimeImmutable()));
        $this->secondEventWithMetadata = new EventWithMetaData(2, new MetaData('', '', new \DateTimeImmutable()));
    }

    public function testLoadAndSaveAggregate()
    {
        $eventStore = $this->mockEventStore();
        $eventsApplier = $this->mockEventsApplierOnAggregate();

        $aggregateRepository = new EventSourcedAggregateRepository(
            $eventStore,
            $eventsApplier
        );

        $aggregate = $aggregateRepository->loadAggregate($this->factoryAggregateDescriptor());

        $this->assertInstanceOf(Aggregate::class, $aggregate);

        $newDecoratedEvents = $aggregateRepository->saveAggregate(self::AGGREGATE_ID, $aggregate, $this->getNewEvents());

        $this->assertEquals(self::AGGREGATE_VERSION + 1, $newDecoratedEvents[0]->getMetaData()->getVersion());
    }

    private function mockEventStore(): EventStore
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsForAggregate')
            ->with($this->equalTo($this->factoryAggregateDescriptor()))
            ->willReturn($this->mockEventStream());


        $eventStore
            ->expects($this->once())
            ->method('appendEventsForAggregate')
            ->with(
                $this->equalTo($this->factoryAggregateDescriptor()),
                $this->equalTo($this->getNewEvents()),
                $this->equalTo($this->mockEventStream()));

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
            $this->aggregateEventStream = $this->getMockBuilder(InMemoryAggregateEventStream::class)
                ->disableOriginalConstructor()
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
        return [
            $this->firstEventWithMetadata,
            $this->secondEventWithMetadata,
        ];
    }

    private function factoryAggregateDescriptor(): AggregateDescriptor
    {
        static $cache = null;
        if (!$cache) {
            $cache = new AggregateDescriptor(self::AGGREGATE_ID, Aggregate::class);
        }
        return $cache;
    }
}

class Aggregate
{

}


