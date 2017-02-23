<?php


namespace tests;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\EventStore\AggregateEventStream;
use Gica\Cqrs\EventStore\EventStream;
use Gica\Cqrs\EventStore\Exception\ConcurrentModificationException;
use Gica\Cqrs\EventStore\InMemory\InMemoryEventStore;


class InMemoryEventStoreTest extends \PHPUnit_Framework_TestCase
{

    public function test_appendEventsForAggregate()
    {
        $store = new InMemoryEventStore();

        $event1 = new Event1();

        $event2 = new Event2;

        $aggregateClass = \stdClass::class;
        $aggregateId = 123;
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

        $store->appendEventsForAggregate($aggregateId, $aggregateClass, $eventsWithMetaData1, 0, 0);

        $this->assertEquals(1, $store->getAggregateVersion($aggregateClass, $aggregateId));
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

        $store->appendEventsForAggregate($aggregateId, $aggregateClass, $eventsWithMetaData2, 1, 1);

        $this->assertEquals(2, $store->getAggregateVersion($aggregateClass, $aggregateId));
        $this->assertEquals(2, $store->fetchLatestSequence());

        $events = $store->loadEventsByClassNames([get_class($event2)]);

        $this->assertInstanceOf(EventStream::class, $events);
        $this->assertCount(2, iterator_to_array($events));

        $eventsforAggregate = $store->loadEventsForAggregate($aggregateClass, $aggregateId);

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

        $store->appendEventsForAggregate($aggregateId, $aggregateClass, $eventsWithMetaData, 0, 0);

        $store->appendEventsForAggregate($aggregateId, $aggregateClass, $eventsWithMetaData, 0, 0);
    }

    public function test_appendEventsForAggregateWithoutChecking()
    {
        $store = new InMemoryEventStore();

        $event1 = new Event1();

        $event2 = new Event2;

        $aggregateClass = \stdClass::class;
        $aggregateId = 123;

        $store->appendEventsForAggregateWithoutChecking($aggregateId, $aggregateClass, [$event1, $event2], 0, 0);

        $this->assertEquals(1, $store->getAggregateVersion($aggregateClass, $aggregateId));
        $this->assertEquals(1, $store->fetchLatestSequence());

        $store->appendEventsForAggregateWithoutChecking($aggregateId, $aggregateClass, [$event1, $event2], 1, 1);

        $this->assertEquals(2, $store->getAggregateVersion($aggregateClass, $aggregateId));
        $this->assertEquals(2, $store->fetchLatestSequence());
    }
}

class Event1 implements Event
{

}

class Event2 implements Event
{

}