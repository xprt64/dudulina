<?php


namespace tests;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore\AggregateEventStream;
use Dudulina\EventStore\EventStream;
use Dudulina\EventStore\Exception\ConcurrentModificationException;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventStore;


class InMemoryEventStoreTest extends \PHPUnit\Framework\TestCase
{

    public function test_appendEventsForAggregate()
    {
        $store = new InMemoryEventStore();

        $event1 = new Event1();

        $event2 = new Event2;

        $aggregateClass = \stdClass::class;
        $aggregateId = 123;
        
        $descriptor = $this->factoryAggregateDescriptor();
        
        $eventsWithMetaData1 = [
            new EventWithMetaData(
                $event1,
                new MetaData(
                    $aggregateId,
                    $aggregateClass,
                    new \DateTimeImmutable(),
                    null
                )
            ),
        ];

        $expectedStream = $store->factoryAggregateEventStream($descriptor);
        
        $store->appendEventsForAggregate($this->factoryAggregateDescriptor(), $eventsWithMetaData1, $expectedStream);

        $this->assertEquals(1, $store->getAggregateVersion($descriptor));
        $this->assertEquals(1, $store->fetchLatestSequence());

        $eventsWithMetaData2 = [
            new EventWithMetaData(
                $event2,
                new MetaData(
                    $aggregateId,
                    $aggregateClass,
                    new \DateTimeImmutable(),
                    null
                )
            ),
            new EventWithMetaData(
                $event2,
                new MetaData(
                    $aggregateId,
                    $aggregateClass,
                    new \DateTimeImmutable(),
                    null
                )
            ),
        ];

        $store->appendEventsForAggregate($descriptor, $eventsWithMetaData2, $expectedStream->withIncrementedVersion()->withIncrementedSequence());

        $this->assertEquals(2, $store->getAggregateVersion($descriptor));
        $this->assertEquals(2, $store->fetchLatestSequence());

        $events = $store->loadEventsByClassNames([get_class($event2)]);

        $this->assertInstanceOf(EventStream::class, $events);
        $this->assertCount(2, iterator_to_array($events));

        $eventsforAggregate = $store->loadEventsForAggregate($descriptor);

        $this->assertInstanceOf(AggregateEventStream::class, $eventsforAggregate);
        $this->assertCount(3, iterator_to_array($eventsforAggregate));
    }

    public function test_appendEventsForAggregateWithConcurrentModificationException()
    {
        $store = new InMemoryEventStore();

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateClass = \stdClass::class;
        $aggregateId = 123;
        $eventsWithMetaData = [
            new EventWithMetaData(
                $event1,
                new MetaData(
                    $aggregateId,
                    $aggregateClass,
                    new \DateTimeImmutable(),
                    null
                )
            ),
        ];

        $this->expectException(ConcurrentModificationException::class);
       
        $expectedStream = $store->factoryAggregateEventStream($this->factoryAggregateDescriptor());

        $store->appendEventsForAggregate($this->factoryAggregateDescriptor(), $eventsWithMetaData, $expectedStream);

        $store->appendEventsForAggregate($this->factoryAggregateDescriptor(), $eventsWithMetaData, $expectedStream);
    }

    public function test_appendEventsForAggregateWithoutChecking()
    {
        $store = new InMemoryEventStore();

        $expectedStream = $store->factoryAggregateEventStream($this->factoryAggregateDescriptor());

        $event1 = new Event1();

        $event2 = new Event2;

        $aggregateClass = \stdClass::class;
        $aggregateId = 123;

        $store->appendEventsForAggregateWithoutChecking($this->factoryAggregateDescriptor(), [$event1, $event2], $expectedStream);

        $this->assertEquals(1, $store->getAggregateVersion($this->factoryAggregateDescriptor()));
        $this->assertEquals(1, $store->fetchLatestSequence());

        $store->appendEventsForAggregateWithoutChecking($this->factoryAggregateDescriptor(), [$event1, $event2], $expectedStream->withIncrementedVersion()->withIncrementedSequence());

        $this->assertEquals(2, $store->getAggregateVersion($this->factoryAggregateDescriptor()));
        $this->assertEquals(2, $store->fetchLatestSequence());
    }

    public function test_findEventById()
    {
        $store = new InMemoryEventStore();

        $expectedStream = $store->factoryAggregateEventStream($this->factoryAggregateDescriptor());

        $event1 = new Event1();

        $event2 = new Event2();

        $aggregateClass = \stdClass::class;
        $aggregateId = 123;

        $eventWithMetaData1 = new EventWithMetaData(
            $event1,
            (new MetaData(
                $aggregateId,
                $aggregateClass,
                new \DateTimeImmutable(),
                null
            ))->withEventId('eventId1')
        );

        $eventsWithMetaData = [
            $eventWithMetaData1,
            new EventWithMetaData(
                $event2,
                new MetaData(
                    $aggregateId,
                    $aggregateClass,
                    new \DateTimeImmutable(),
                    null
                )
            ),
        ];

        $store->appendEventsForAggregate($this->factoryAggregateDescriptor(), $eventsWithMetaData, $expectedStream);

        $this->assertNull($store->findEventById('nonExistentId'));
        $this->assertSame($eventWithMetaData1, $store->findEventById('eventId1'));
    }

    private function factoryAggregateDescriptor(): AggregateDescriptor
    {
        return new AggregateDescriptor(123, \stdClass::class);
    }
}

class Event1 implements Event
{

}

class Event2 implements Event
{

}