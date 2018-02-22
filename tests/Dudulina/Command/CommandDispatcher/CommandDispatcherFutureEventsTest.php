<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Dudulina\Command\CommandDispatcher\CommandDispatcherFutureEventsTest;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\MetadataFactory\DefaultMetadataWrapper;
use Dudulina\Event;
use Dudulina\Event\EventDispatcher\EventDispatcherBySubscriber;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetadataFactory\DefaultMetadataFactory;
use Dudulina\Event\ScheduledEvent;
use Dudulina\EventStore\InMemory\InMemoryEventStore;
use Dudulina\FutureEventsStore;

class CommandDispatcherFutureEventsTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    private function factoryAggregateDescriptor(): AggregateDescriptor
    {
        return new AggregateDescriptor(self::AGGREGATE_ID, Aggregate1::class);
    }

    public function test_dispatchCommand()
    {
        $aggregateId = self::AGGREGATE_ID;
        $aggregateClass = Aggregate1::class;

        $command = $this->mockCommand();

        $commandSubscriber = $this->mockCommandSubscriber();

        $eventDispatcher = $this->mockEventDispatcher();

        $eventStore = new InMemoryEventStore();

        $eventsApplierOnAggregate = new EventsApplierOnAggregate();

        $commandApplier = new CommandApplier();

        $aggregateRepository = new AggregateRepository($eventStore, $eventsApplierOnAggregate);

        $concurrentProofFunctionCaller = new ConcurrentProofFunctionCaller;

        $futureEventsStore = new StubFutureEventsStore();

        $commandDispatcher = new DefaultCommandDispatcher(
            $commandSubscriber,
            $eventDispatcher,
            $commandApplier,
            $aggregateRepository,
            $concurrentProofFunctionCaller,
            $eventsApplierOnAggregate,
            new DefaultMetadataFactory(),
            new DefaultMetadataWrapper(),
            $futureEventsStore
        );

        $commandDispatcher->dispatchCommand($command);

        $this->assertCount(1, $eventStore->loadEventsForAggregate($this->factoryAggregateDescriptor()));

        $this->assertCount(1, $futureEventsStore->scheduledEvents);

        /** @var EventWithMetaData $eventWithMetadata */
        $eventWithMetadata = $futureEventsStore->scheduledEvents[0];

        $this->assertInstanceOf(EventInTheFuture::class, $eventWithMetadata->getEvent());

        $this->assertCount(1, $eventStore->loadEventsForAggregate($this->factoryAggregateDescriptor()));
    }

    private function mockCommand(): Command
    {
        $command = $this->getMockBuilder(Command1::class)
            ->disableOriginalConstructor()
            ->getMock();
        $command->expects($this->any())
            ->method('getAggregateId')
            ->willReturn(self::AGGREGATE_ID);

        /** @var Command $command */
        return $command;
    }

    private function mockCommandSubscriber(): CommandSubscriber
    {
        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber->expects($this->any())
            ->method('getHandlerForCommand')
            ->with($this->isInstanceOf(Command1::class))
            ->willReturn(new Command\ValueObject\CommandHandlerDescriptor(
                Aggregate1::class,
                'handleCommand1'
            ));

        /** @var CommandSubscriber $commandSubscriber */
        return $commandSubscriber;
    }

    private function mockEventDispatcher(): EventDispatcherBySubscriber
    {
        $eventDispatcher = $this->getMockBuilder(EventDispatcherBySubscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcher->expects($this->once())
            ->method('dispatchEvent')
            ->with($this->isInstanceOf(EventWithMetaData::class));

        /** @var EventDispatcherBySubscriber $eventDispatcher */
        return $eventDispatcher;
    }
}

class StubFutureEventsStore implements FutureEventsStore
{
    public $scheduledEvents;

    public function loadAndProcessScheduledEvents(callable $eventProcessor/** function(ScheduledEventWithMetadata) */)
    {
    }

    /**
     * @param \Dudulina\Event\EventWithMetaData[] $eventWithMetaData
     */
    public function scheduleEvents($eventWithMetaData)
    {
        $this->scheduledEvents = $eventWithMetaData;
    }

    public function scheduleEvent(Event\EventWithMetaData $eventWithMetaData, \DateTimeImmutable $date)
    {
    }
}

class Command1 implements \Dudulina\Command
{
    /**
     * @var
     */
    private $aggregateId;

    public function __construct(
        $aggregateId
    )
    {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }
}

class Aggregate1
{
    public function handleCommand1(Command1 $command1)
    {
        yield new Event1($command1->getAggregateId());
        yield new EventInTheFuture($command1->getAggregateId());
    }

    public function applyEventInTheFuture(EventInTheFuture $event)
    {
        throw new \Exception("Should not be applied now");
    }
}


class EventInTheFuture implements Event, ScheduledEvent
{
    /**
     * @var
     */
    private $aggregateId;

    public function __construct(
        $aggregateId
    )
    {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    public function getFireDate(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->add(new \DateInterval('P1Y'));
    }

    public function getMessageId()
    {
        return 124;
    }
}

class Event1 implements Event
{
    /**
     * @var
     */
    private $aggregateId;

    public function __construct(
        $aggregateId
    )
    {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }
}
