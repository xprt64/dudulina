<?php


namespace tests\Dudulina\FutureEvents;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Dudulina\Event;
use Dudulina\Event\EventDispatcher;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore\InMemory\InMemoryEventStore;
use Dudulina\FutureEventsStore;
use Dudulina\Scheduling\ScheduledEventsPlayer;
use Dudulina\Scheduling\ScheduledEventWithMetadata;


class ScheduledEventsPlayerTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    const AGGREGATE_CLASS = 'aggregateClass';

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
                self::AGGREGATE_ID,
                self::AGGREGATE_CLASS,
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

        $repository = $this->getMockBuilder(AggregateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('saveAggregate')
        ->with(
            $this->equalTo(self::AGGREGATE_ID),
            $this->anything(),
            $this->equalTo([$eventWithMetadata])
        );


        $eventStore = new InMemoryEventStore();

        /** @var FutureEventsStore $futureEventStore */
        /** @var EventDispatcher $eventDispatcher */
        /** @var AggregateRepository $repository */

        $sut = new ScheduledEventsPlayer(
            $futureEventStore,
            $eventDispatcher,
            new ConcurrentProofFunctionCaller(),
            $repository
        );

        $sut->run();

//        $this->assertSame(1, $eventStore->getAggregateVersion($this->factoryAggregateDescriptor()));
//        $this->assertSame(1, $eventStore->fetchLatestSequence());
    }

    private function factoryAggregateDescriptor(): AggregateDescriptor
    {
        return new AggregateDescriptor(self::AGGREGATE_ID, self::AGGREGATE_CLASS);
    }
}
