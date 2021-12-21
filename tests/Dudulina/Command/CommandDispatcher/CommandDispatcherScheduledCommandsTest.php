<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Dudulina\Command\CommandDispatcher\CommandDispatcherScheduledCommandsTest;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\EventSourcedAggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher;
use Dudulina\Command\CommandDispatcher\SideEffectsDispatcher\DefaultSideEffectsDispatcher;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\MetadataFactory\DefaultMetadataWrapper;
use Dudulina\Event;
use Dudulina\Event\EventDispatcher\EventDispatcherBySubscriber;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetadataFactory\DefaultMetadataFactory;
use Dudulina\Scheduling\ScheduledCommand;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventStore;

class CommandDispatcherScheduledCommandsTest extends \PHPUnit\Framework\TestCase
{

    const AGGREGATE_ID = 123;

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

        $aggregateRepository = new EventSourcedAggregateRepository($eventStore, $eventsApplierOnAggregate);

        $concurrentProofFunctionCaller = new ConcurrentProofFunctionCaller;

        $scheduledCommandStore = new StubScheduledCommandStore();

        $commandDispatcher = new DefaultCommandDispatcher(
            $commandSubscriber,
            $commandApplier,
            $aggregateRepository,
            $concurrentProofFunctionCaller,
            $eventsApplierOnAggregate,
            new DefaultMetadataFactory(),
            new DefaultMetadataWrapper(),
            new DefaultSideEffectsDispatcher($eventDispatcher, $scheduledCommandStore)
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

class StubScheduledCommandStore implements \Dudulina\Scheduling\CommandScheduler
{
    /**
     * @var ScheduledCommand[]
     */
    private $commands = [];

    public function loadAndProcessScheduledCommands(callable $eventProcessor/** function(ScheduledCommand $scheduledCommand) */)
    {
    }

    /**
     * @return ScheduledCommand[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    public function scheduleCommand(ScheduledCommand $scheduledCommand, AggregateDescriptor $aggregateDescriptor, $commandMetadata = null)
    {
        $this->commands[] = $scheduledCommand;
    }

    public function cancelCommand($commandId)
    {
        foreach ($this->commands as $i => $scheduledCommand) {
            if ((string)$scheduledCommand->getMessageId() === (string)$commandId) {
                unset($this->commands[$i]);
            }
        }
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
