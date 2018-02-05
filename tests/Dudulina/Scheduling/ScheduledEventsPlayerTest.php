<?php


namespace tests\Dudulina\FutureEvents;


use Dudulina\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Dudulina\Event;
use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore\InMemory\InMemoryEventStore;
use Dudulina\FutureEventsStore;
use Dudulina\Scheduling\ScheduledEventWithMetadata;


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

        $sut = new \Dudulina\Scheduling\ScheduledEventsPlayer(
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
