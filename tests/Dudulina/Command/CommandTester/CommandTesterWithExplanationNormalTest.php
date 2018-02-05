<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Command\CommandTesterWithExplanation;


use Dudulina\Aggregate\AggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\MetadataFactory\DefaultMetadataWrapper;
use Dudulina\Event;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\MetadataFactory\DefaultMetadataFactory;
use Dudulina\EventStore\InMemory\InMemoryEventStore;

class CommandTesterWithExplanationNormalTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    public function test_dispatchCommand()
    {
        $aggregateId = self::AGGREGATE_ID;
        $aggregateClass = Aggregate1::class;

        $command = $this->mockCommand();

        $commandSubscriber = $this->mockCommandSubscriber();

        $eventStore = new InMemoryEventStore($aggregateClass, $aggregateId);

        $eventStore->appendEventsForAggregate(
            $aggregateId,
            $aggregateClass,
            $eventStore->decorateEventsWithMetadata(
                $aggregateClass, $aggregateId, [new Event0($aggregateId)]
            ),
            0,
            0
        );

        $eventsApplierOnAggregate = new EventsApplierOnAggregate();

        $commandApplier = new CommandApplier();

        $aggregateRepository = new AggregateRepository($eventStore, $eventsApplierOnAggregate);

        Aggregate1::$state = 0;

        $commandDispatcher = new Command\CommandTester\DefaultCommandTesterWithExplanation(
            $commandSubscriber,
            $commandApplier,
            $aggregateRepository,
            $eventsApplierOnAggregate,
            new DefaultMetadataFactory(),
            new DefaultMetadataWrapper()
        );

        $this->assertEquals(0, Aggregate1::$state);
        $this->assertCount(1, $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId));

        $this->assertEmpty($commandDispatcher->whyCantExecuteCommand($command));
        $this->assertCount(1, $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId));
        $this->assertEquals(2, Aggregate1::$state);//state is modified but none is persisted
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
    public static $state = 0;

    public function handleCommand1(Command1 $command1)
    {
        yield new Event1($command1->getAggregateId());
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

    /**
     * @return mixed
     */
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
