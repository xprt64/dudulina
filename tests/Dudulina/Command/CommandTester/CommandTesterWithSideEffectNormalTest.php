<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Command\CommandTester\CommandTesterWithSideEffectNormalTest;


use Dudulina\Aggregate\AggregateDescriptor;
use Dudulina\Aggregate\EventSourcedAggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\CommandTester\DefaultCommandTesterWithSideEffect;
use Dudulina\Command\MetadataFactory\DefaultMetadataWrapper;
use Dudulina\Event;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\MetadataFactory\DefaultMetadataFactory;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventStore;

class CommandTesterWithSideEffectNormalTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    private function factoryAggregateDescriptor()
    {
        return new AggregateDescriptor(self::AGGREGATE_ID, Aggregate1::class);
    }

    public function test_dispatchCommand()
    {
        $aggregateId = self::AGGREGATE_ID;
        $aggregateClass = Aggregate1::class;

        $command = $this->mockCommand();
        $command2 = new Command2($aggregateId);

        $commandSubscriber = $this->mockCommandSubscriber();

        $eventStore = new InMemoryEventStore();

        $eventStore->appendEventsForAggregate(
            $this->factoryAggregateDescriptor(),
            $eventStore->decorateEventsWithMetadata(
                $this->factoryAggregateDescriptor(), [new Event0($aggregateId)]
            ),
            $eventStore->factoryAggregateEventStream($this->factoryAggregateDescriptor())
        );

        $eventsApplierOnAggregate = new EventsApplierOnAggregate();

        $commandApplier = new CommandApplier();

        $aggregateRepository = new EventSourcedAggregateRepository($eventStore, $eventsApplierOnAggregate);

        Aggregate1::$state = 0;

        $commandTester = new DefaultCommandTesterWithSideEffect(
            $commandSubscriber,
            $commandApplier,
            $aggregateRepository,
            $eventsApplierOnAggregate,
            new DefaultMetadataFactory(),
            new DefaultMetadataWrapper()
        );

        $this->assertEquals(0, Aggregate1::$state);
        $this->assertCount(1, $eventStore->loadEventsForAggregate($this->factoryAggregateDescriptor()));

        $this->assertTrue($commandTester->shouldExecuteCommand($command));
        $this->assertCount(1, $eventStore->loadEventsForAggregate($this->factoryAggregateDescriptor()));
        $this->assertEquals(2, Aggregate1::$state);//state is modified but none is persisted

        $this->assertFalse($commandTester->shouldExecuteCommand($command2));
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
            ->willReturnCallback(function ($command) {
                if ($command instanceof Command1) {
                    return new Command\ValueObject\CommandHandlerDescriptor(
                        Aggregate1::class,
                        'handleCommand1'
                    );
                }
                if ($command instanceof Command2) {
                    return new Command\ValueObject\CommandHandlerDescriptor(
                        Aggregate1::class,
                        'handleCommand2'
                    );
                }
                $this->fail("Unknown command class " . get_class($command));
                return '';
            });

        /** @var CommandSubscriber $commandSubscriber */
        return $commandSubscriber;
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

class Command2 implements \Dudulina\Command
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
    public static $state = 0;

    public function handleCommand1(Command1 $command1)
    {
        yield new Event1($command1->getAggregateId());
    }

    public function handleCommand2(Command2 $command1)
    {
        return;

        //intentionally yielding something
        yield "something";
    }

    public function applyEvent0(Event0 $event)
    {
        self::$state++;
    }

    public function applyEvent1(Event1 $event)
    {
        self::$state++;
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

class Event0 implements Event
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

    /**
     * @return mixed
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }
}
