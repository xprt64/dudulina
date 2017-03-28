<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Gica\Cqrs\Command\CommandDispatcher\CommandDispatcherFutureEventsTest;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandApplier;
use Gica\Cqrs\Command\CommandDispatcher;
use Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Command\CommandDispatcher\DefaultCommandDispatcher;
use Gica\Cqrs\Command\CommandSubscriber;
use Gica\Cqrs\Command\CommandValidator;
use Gica\Cqrs\Command\MetadataFactory\DefaultMetadataWrapper;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher\EventDispatcherBySubscriber;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetadataFactory\DefaultMetadataFactory;
use Gica\Cqrs\Event\ScheduledEvent;
use Gica\Cqrs\EventStore\InMemory\InMemoryEventStore;
use Gica\Cqrs\FutureEventsStore;

class CommandDispatcherFutureEventsTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    public function test_dispatchCommand()
    {
        $aggregateId = self::AGGREGATE_ID;
        $aggregateClass = Aggregate1::class;

        $command = $this->mockCommand();

        $commandSubscriber = $this->mockCommandSubscriber();

        $eventDispatcher = $this->mockEventDispatcher();

        $eventStore = new InMemoryEventStore($aggregateClass, $aggregateId);

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

        $this->assertCount(1, $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId));

        $this->assertCount(1, $futureEventsStore->scheduledEvents);

        /** @var EventWithMetaData $eventWithMetadata */
        $eventWithMetadata = $futureEventsStore->scheduledEvents[0];

        $this->assertInstanceOf(EventInTheFuture::class, $eventWithMetadata->getEvent());

        $this->assertTrue($commandDispatcher->canExecuteCommand($command));
        $this->assertCount(1, $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId));
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
     * @param \Gica\Cqrs\Event\EventWithMetaData[] $eventWithMetaData
     */
    public function scheduleEvents($eventWithMetaData)
    {
        $this->scheduledEvents = $eventWithMetaData;
    }

    public function scheduleEvent(Event\EventWithMetaData $eventWithMetaData, \DateTimeImmutable $date)
    {
    }
}

class Command1 implements \Gica\Cqrs\Command
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
