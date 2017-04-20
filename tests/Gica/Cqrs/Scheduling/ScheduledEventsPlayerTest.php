<?php


namespace tests\Gica\Cqrs\FutureEvents;


use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore\InMemory\InMemoryEventStore;
use Gica\Cqrs\FutureEventsStore;
use Gica\Cqrs\Scheduling\ScheduledEventWithMetadata;


class ScheduledEventsPlayerTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $futureEventStore = $this->getMockBuilder(FutureEventsStore::class)
            ->getMock();

        /** @var EventWithMetaData $eventWithMetadata */
        /** @var Event $event */

        $event = $this->getMockBuilder(Event::class)->getMock();

        $eventWithMetadata = new EventWithMetaData(
            $event,
            new Event\MetaData(
                123,
                'aggregateClass',
                new \DateTimeImmutable(),
                null
            )
        );


        $futureEventStore->expects($this->once())
            ->method('loadAndProcessScheduledEvents')
            ->willReturnCallback(function ($processor) use ($eventWithMetadata) {
                call_user_func($processor, new ScheduledEventWithMetadata(1, $eventWithMetadata));
            });

        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->getMock();
        $eventDispatcher->expects($this->once())
            ->method('dispatchEvent')
            ->with($eventWithMetadata);


        $eventStore = new InMemoryEventStore();

        /** @var FutureEventsStore $futureEventStore */
        /** @var EventDispatcher $eventDispatcher */

        $sut = new \Gica\Cqrs\Scheduling\ScheduledEventsPlayer(
            $futureEventStore,
            $eventDispatcher,
            $eventStore,
            new ConcurrentProofFunctionCaller()
        );

        $sut->run();

        $this->assertSame(1, $eventStore->getAggregateVersion('aggregateClass', 123));
        $this->assertSame(1, $eventStore->fetchLatestSequence());
    }
}
