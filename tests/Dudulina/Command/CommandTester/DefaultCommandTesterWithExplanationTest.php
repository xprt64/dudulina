<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Command\CommandTesterWithExplanation\CommandDispatcherCanExecuteCommandCommandNotAcceptedTest;


use Dudulina\Aggregate\EventSourcedAggregateRepository;
use Dudulina\Command;
use Dudulina\Command\CommandApplier;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\CommandTester\DefaultCommandTesterWithExplanation;
use Dudulina\Command\MetadataFactory\DefaultMetadataWrapper;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Event\MetadataFactory\DefaultMetadataFactory;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventStore;

class DefaultCommandTesterWithExplanationTest extends \PHPUnit\Framework\TestCase
{

    const AGGREGATE_ID = 123;

    /** @var Command */
    private $command;

    protected function setUp():void
    {
        $this->command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->command->expects($this->any())
            ->method('getAggregateId')
            ->willReturn(self::AGGREGATE_ID);
    }

    public function test_canExecuteCommandNotAcceptedCommand()
    {
        $aggregateId = self::AGGREGATE_ID;
        $aggregateClass = Aggregate1::class;

        $commandSubscriber = $this->mockCommandSubscriber();

        $eventStore = new InMemoryEventStore();

        $eventsApplierOnAggregate = new EventsApplierOnAggregate();

        $commandApplier = new CommandApplier();

        $aggregateRepository = new EventSourcedAggregateRepository($eventStore, $eventsApplierOnAggregate);

        Aggregate1::$expectedCommand = $this->command;
        Aggregate1::$throwedException = new \Exception("Command not accepted");

        $commandDispatcher = new DefaultCommandTesterWithExplanation(
            $commandSubscriber,
            $commandApplier,
            $aggregateRepository,
            $eventsApplierOnAggregate,
            new DefaultMetadataFactory(),
            new DefaultMetadataWrapper()
        );

        $errors = $commandDispatcher->whyCantExecuteCommand($this->command);

        $this->assertCount(1, $errors);

        $this->assertSame(Aggregate1::$throwedException, $errors[0]);
    }

    private function mockCommandSubscriber(): CommandSubscriber
    {
        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber->expects($this->any())
            ->method('getHandlerForCommand')
            ->with($this->equalTo($this->command))
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

    /**
     * @var Command
     */
    public static $expectedCommand;

    /**
     * @var \Exception
     */
    public static $throwedException;

    public function handleCommand1($command1)
    {
        throw self::$throwedException;

        /** @noinspection PhpUnreachableStatementInspection */
        yield "event";
    }
}