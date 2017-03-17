<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Gica\Cqrs\Command\CommandDispatcher\CommandDispatcherScheduledCommandsTest;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandApplier;
use Gica\Cqrs\Command\CommandDispatcher;
use Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Command\CommandSubscriber;
use Gica\Cqrs\Command\CommandValidator;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher\EventDispatcherBySubscriber;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore\InMemory\InMemoryEventStore;
use Gica\Cqrs\Scheduling\ScheduledCommand;
use Gica\Cqrs\Scheduling\ScheduledCommandStore;

class CommandDispatcherScheduledCommandsTest extends \PHPUnit_Framework_TestCase
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

        /** @var \Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService $authenticatedIdentity */
        $authenticatedIdentity = $this->getMockBuilder(AuthenticatedIdentityReaderService::class)
            ->getMock();

        $scheduledCommandStore = new StubScheduledCommandStore();

        /** @var CommandValidator $commandValidator */
        $commandValidator = $this->getMockBuilder(CommandValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandDispatcher = new CommandDispatcher(
            $commandSubscriber,
            $eventDispatcher,
            $commandApplier,
            $aggregateRepository,
            $concurrentProofFunctionCaller,
            $commandValidator,
            $authenticatedIdentity,
            null,
            $eventsApplierOnAggregate,
            $scheduledCommandStore
        );

        $commandDispatcher->dispatchCommand($command);

        $this->assertCount(1, $scheduledCommandStore->getCommands());

        $scheduledCommand = $scheduledCommandStore->getCommands()[0];

        $this->assertInstanceOf(CommandInTheFuture::class, $scheduledCommand);
    }

    private function mockCommand(): Command1
    {
        return new Command1(self::AGGREGATE_ID);
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

class StubScheduledCommandStore implements ScheduledCommandStore
{
    /**
     * @var ScheduledCommand[]
     */
    private $commands = [];

    public function loadAndProcessScheduledCommands(callable $eventProcessor/** function(ScheduledCommand $scheduledCommand) */)
    {
    }

    /**
     * @param ScheduledCommand[] $scheduledCommands
     */
    public function scheduleCommands($scheduledCommands)
    {
        $this->commands = array_merge($this->commands, $scheduledCommands);
    }

    /**
     * @return ScheduledCommand[]
     */
    public function getCommands()
    {
        return $this->commands;
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
    private $appliedCount = 0;

    public function handleCommand1(Command1 $command1)
    {
        yield new Event1($command1->getAggregateId());
        yield new CommandInTheFuture($command1->getAggregateId());
    }

    public function applyEvent1(Event1 $event)
    {
        $event->getAggregateId();
        $this->appliedCount++;
    }

    public function getAppliedCount(): int
    {
        return $this->appliedCount;
    }
}


class CommandInTheFuture implements ScheduledCommand
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
