<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Gica\Cqrs\Command;


use Gica\Cqrs\Aggregate\AggregateRepository;
use Gica\Cqrs\Command\CommandApplier;
use Gica\Cqrs\Command\CommandDispatcher;
use Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Command\CommandValidator;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventDispatcher\EventDispatcherBySubscriber;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnAggregate;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\FutureEventsStore;
use Gica\Types\Guid;

class CommandDispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function test_dispatchCommand()
    {
        $aggregateId = new Guid;
        $aggregateClass = 'agg';

        $command = new Command1($aggregateId);

        $commandSubscriber = new MockCommandSubscriber();

        $eventDispatcher = new EventDispatcherBySubscriber(new MockEventSubscriber());

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
            $futureEventsStore,
            $eventsApplierOnAggregate
        );

        $commandDispatcher->dispatchCommand($command);

        $this->assertEquals(2, Aggregate1::$state);
        $this->assertEquals(1, MockRead1::$handledCount);
        $this->assertCount(2, $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId));

    }

    /**
     * @expectedException \Gica\Cqrs\Command\Exception\TooManyCommandExecutionRetries
     */
    public function test_dispatchCommandShouldNotSuccessOnConcurencyException()
    {
        $aggregateId = new Guid;
        $aggregateClass = 'agg';

        $command = new CommandForConcurencyException($aggregateId);

        $commandSubscriber = new MockCommandSubscriber();

        $eventDispatcher = new EventDispatcherBySubscriber(new MockEventSubscriber());

        $eventStore = new InMemoryEventStore($aggregateClass, $aggregateId);

        Aggregate1ForConcurencyTest::$eventStore = $eventStore;

        $eventsApplierOnAggregate = new EventsApplierOnAggregate();

        $commandApplier = new CommandApplier();

        $aggregateRepository = new AggregateRepository($eventStore, $eventsApplierOnAggregate);

        $concurrentProofFunctionCaller = new ConcurrentProofFunctionCaller;

        /** @var AuthenticatedIdentityReaderService $authenticatedIdentityReaderService */
        $authenticatedIdentityReaderService = $this->getMockBuilder(AuthenticatedIdentityReaderService::class)
            ->getMock();

        /** @var FutureEventsStore $futureEventsStore */
        $futureEventsStore = $this->getMockBuilder(FutureEventsStore::class)
            ->getMock();

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
            $authenticatedIdentityReaderService,
            $futureEventsStore,
            $eventsApplierOnAggregate);

        $commandDispatcher->dispatchCommand($command);

        $this->assertEquals(2, Aggregate1::$state);
        $this->assertEquals(1, MockRead1::$handledCount);
        $this->assertCount(2, $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId));

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

    /**
     * @return mixed
     */
    public function getAggregateId(): Guid
    {
        return $this->aggregateId;
    }
}

class CommandForConcurencyException implements \Gica\Cqrs\Command
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
    public function getAggregateId(): Guid
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


class Aggregate1ForConcurencyTest
{
    public static $state = 0;

    /** @var  InMemoryEventStore */
    public static $eventStore;

    public function handleCommandForConcurencyException(CommandForConcurencyException $command)
    {
        yield new Event1($command->getAggregateId());

        self::$eventStore->appendEventsForAggregateWithoutChecking($command->getAggregateId(), 'agg', [new Event1($command->getAggregateId())]);
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


class MockCommandSubscriber implements \Gica\Cqrs\Command\CommandSubscriber
{

    /**
     * @inheritdoc
     */
    public function getHandlerForCommand(\Gica\Cqrs\Command $command)
    {
        if ($command instanceof Command1) {
            return new \Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor(Aggregate1::class, 'handleCommand1');
        }
        if ($command instanceof CommandForConcurencyException) {
            return new \Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor(Aggregate1ForConcurencyTest::class, 'handleCommandForConcurencyException');
        }

        throw new \Exception("Handler not found");
    }
}

class MockRead1
{
    public static $handledCount = 0;

    public function onEvent1(Event1 $event)
    {
        self::$handledCount++;
    }
}

class InMemoryEventStore implements \Gica\Cqrs\EventStore
{
    public $events = [];
    private $versions = [];
    private $latestSequence = -1;


    public function __construct(string $aggregateClass, $aggregateId)
    {
        $this->addEventsToArrayForAggregate($aggregateId, $aggregateClass, $this->decorateEventsWithMetadata($aggregateClass, $aggregateId, [new Event0($aggregateId)]));
    }

    public function loadEventsForAggregate(string $aggregateClass, $aggregateId): \Gica\Cqrs\EventStore\AggregateEventStream
    {
        return new MockEventStream($this->getEventsArrayForAggregate($aggregateId), $aggregateClass, $aggregateId);
    }

    public function appendEventsForAggregate($aggregateId, string $aggregateClass, $eventsWithMetaData, int $expectedVersion, int $expectedSequence)
    {
        if (count($this->getEventsArrayForAggregate($aggregateId)) != $expectedVersion) {
            throw new \Gica\Cqrs\EventStore\Exception\ConcurrentModificationException();
        }

        $this->addEventsToArrayForAggregate($aggregateId, $aggregateClass, $eventsWithMetaData);

        $this->versions[(string)$aggregateId] = $expectedVersion;
        $this->latestSequence = $expectedSequence;
    }

    public function appendEventsForAggregateWithoutChecking($aggregateId, $aggregateClass, $newEvents)
    {
        $this->addEventsToArrayForAggregate($aggregateId, $aggregateClass, $this->decorateEventsWithMetadata($aggregateClass, $aggregateId, $newEvents));
    }

    private function getEventsArrayForAggregate($aggregateId)
    {
        return $this->events[(string)$aggregateId];
    }

    private function addEventsToArrayForAggregate($aggregateId, $aggregateClass, $newEvents)
    {
        foreach ($newEvents as $event) {
            $this->events[(string)$aggregateId][] = $event;
        }
    }

    public function loadEventsByClassNames(array $eventClasses): \Gica\Cqrs\EventStore\EventStream
    {
        // TODO: Implement loadEventsByClassNames() method.
    }

    public function getAggregateVersion(string $aggregateClass, $aggregateId)
    {
        return $this->versions[(string)$aggregateId];
    }

    /**
     * @param $aggregateClass
     * @param $aggregateId
     * @param Event[] $priorEvents
     * @return EventWithMetaData[]
     */
    private function decorateEventsWithMetadata($aggregateClass, $aggregateId, array $priorEvents)
    {
        return array_map(function (Event $event) use ($aggregateClass, $aggregateId) {
            return new EventWithMetaData($event, new \Gica\Cqrs\Event\MetaData(
                $aggregateId, $aggregateClass, new \DateTimeImmutable(), null
            ));
        }, $priorEvents);
    }

    public function fetchLatestSequence(): int
    {
        return $this->latestSequence;
    }
}

class MockEventStream implements \Gica\Cqrs\EventStore\AggregateEventStream
{

    /**
     * @var array
     */
    private $eventsArray;
    private $version;
    private $sequence = 1;
    /**
     * @var
     */
    private $aggregateClass;
    /**
     * @var
     */
    private $aggregateId;

    public function __construct(array $eventsArray, $aggregateClass, $aggregateId)
    {
        $this->version = count($eventsArray);
        $this->aggregateClass = $aggregateClass;
        $this->aggregateId = $aggregateId;
        $this->eventsArray = $eventsArray;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->eventsArray);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }
}

class MockEventSubscriber implements \Gica\Cqrs\Event\EventSubscriber
{

    public function getListenersForEvent(Event $event)
    {
        if (!($event instanceof Event1)) {
            throw new \BadMethodCallException("event is not instance of Event1");
        }

        return [
            [new MockRead1, 'onEvent1'],
        ];
    }
}

class MockCommandValidator extends CommandValidator
{

}
