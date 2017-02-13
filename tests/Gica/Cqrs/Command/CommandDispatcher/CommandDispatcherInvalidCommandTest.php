<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Gica\Cqrs\Command\CommandDispatcher\CommandDispatcherInvalidCommandTest;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandApplier;
use Gica\Cqrs\Command\CommandDispatcher;
use Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Command\CommandSubscriber;
use Gica\Cqrs\Command\CommandValidator;
use Gica\Cqrs\Command\Exception\CommandValidationFailed;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher\EventDispatcherBySubscriber;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore\InMemory\InMemoryEventStore;
use Gica\Cqrs\FutureEventsStore;

class CommandDispatcherInvalidCommandTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    /** @var Command */
    private $command;

    protected function setUp()
    {
        $this->command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->command->expects($this->any())
            ->method('getAggregateId')
            ->willReturn(self::AGGREGATE_ID);
    }

    public function test_dispatchCommandInvalidCommand()
    {
        $aggregateId = self::AGGREGATE_ID;
        $aggregateClass = 'someInexistentClass';

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber->expects($this->never())
            ->method('getHandlerForCommand');
        /** @var CommandSubscriber  $commandSubscriber */

        $eventDispatcher = $this->getMockBuilder(EventDispatcherBySubscriber::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDispatcher->expects($this->never())
            ->method('dispatchEvent');
        /** @var EventDispatcherBySubscriber  $eventDispatcher */

        $eventStore = new InMemoryEventStore($aggregateClass, $aggregateId);

        $eventsApplierOnAggregate = new EventsApplierOnAggregate();

        $commandApplier = new CommandApplier();

        $aggregateRepository = new AggregateRepository($eventStore, $eventsApplierOnAggregate);

        $concurrentProofFunctionCaller = new ConcurrentProofFunctionCaller;

        /** @var \Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService $authenticatedIdentity */
        $authenticatedIdentity = $this->getMockBuilder(AuthenticatedIdentityReaderService::class)
            ->getMock();

        /** @var FutureEventsStore $futureEventsStore */
        $futureEventsStore = $this->getMockBuilder(FutureEventsStore::class)
            ->getMock();

        $commandValidator = $this->getMockBuilder(CommandValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandValidator->expects($this->any())
            ->method('validateCommand')
            ->with($this->equalTo($this->command))
            ->willReturn(['some error']);
        /** @var CommandValidator $commandValidator */

        $commandDispatcher = new CommandDispatcher(
            $commandSubscriber,
            $eventDispatcher,
            $commandApplier,
            $aggregateRepository,
            $concurrentProofFunctionCaller,
            $commandValidator,
            $authenticatedIdentity,
            $futureEventsStore,
            $eventsApplierOnAggregate
        );

        try {
            $commandDispatcher->dispatchCommand($this->command);
            $this->fail("Should throw exception");

        } catch (\Throwable $exception) {
            if (!$exception instanceof CommandValidationFailed) {
                $this->fail("Wrong exception class:" . get_class($exception));
            }
            $this->assertEquals(['some error'], $exception->getErrors());
        }

        $this->assertFalse($commandDispatcher->canExecuteCommand($this->command));
    }
}