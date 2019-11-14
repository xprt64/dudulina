<?php


namespace tests\Dudulina\ReadModel;


use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventSequence;
use Dudulina\Testing\EventStore\InMemory\FilteredRawEventStreamGroupedByCommit;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventsCommit;
use Dudulina\ProgressReporting\TaskProgressReporter;
use Dudulina\ReadModel\ReadModelEventApplier\ErrorReporter;
use Dudulina\ReadModel\ReadModelEventApplier;
use Dudulina\ReadModel\ReadModelEventApplier\ReadModelReflector;
use Dudulina\ReadModel\ReadModelInterface;
use Dudulina\ReadModel\ReadModelRecreator;
use Psr\Log\LoggerInterface;


class ReadModelRecreatorTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        /** @var MetaData $metadata */
        $metadata = new MetaData(
            'someId',
            'someClass',
            new \DateTimeImmutable()
        );


        /** @var EventWithMetaData[] $events */
        $events = [
            new EventWithMetaData(new Event1, $metadata->withEventId(1)),
            new EventWithMetaData(new Event2, $metadata->withEventId(2)),
        ];

        $eventStream = new FilteredRawEventStreamGroupedByCommit([new InMemoryEventsCommit(1, 1, $events)]);

        /** @var \Dudulina\ProgressReporting\TaskProgressReporter $taskProgressReporter */
        $taskProgressReporter = $this->getMockBuilder(TaskProgressReporter::class)
            ->getMock();

        /** @var LoggerInterface $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        /** @var ErrorReporter $errorReporter */
        $errorReporter = $this->getMockBuilder(ErrorReporter::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->with([Event1::class, Event2::class])
            ->willReturn($eventStream);

        /** @var EventStore $eventStore */
        $sut = new ReadModelRecreator(
            $eventStore,
            $logger,
            new ReadModelEventApplier(
                $errorReporter,
                new ReadModelReflector()
            ),
            new ReadModelReflector()
        );

        $sut->setTaskProgressReporter($taskProgressReporter);

        $readModel = new ReadModel();

        $sut->recreateRead($readModel);

        $this->assertSame(1, $readModel->onEvent1Called);
        $this->assertSame(1, $readModel->onEvent2Called);
    }

    public function testPoll()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        /** @var MetaData $metadata */
        $metadata = new MetaData(
            'someId',
            'someClass',
            new \DateTimeImmutable()
        );


        /** @var EventWithMetaData[] $events */
        $events = [
            new EventWithMetaData(new Event1, $metadata->withSequence(new InMemoryEventSequence(100, 0))),
            new EventWithMetaData(new Event2, $metadata->withSequence(new InMemoryEventSequence(100, 1))),
        ];

        $eventStream = new FilteredRawEventStreamGroupedByCommit([new InMemoryEventsCommit(100, 1, $events)]);

        /** @var \Dudulina\ProgressReporting\TaskProgressReporter $taskProgressReporter */
        $taskProgressReporter = $this->getMockBuilder(TaskProgressReporter::class)
            ->getMock();

        /** @var LoggerInterface $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        /** @var ErrorReporter $errorReporter */
        $errorReporter = $this->getMockBuilder(ErrorReporter::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->with([Event1::class, Event2::class])
            ->willReturn($eventStream);

        /** @var EventStore $eventStore */
        $sut = new ReadModelRecreator(
            $eventStore,
            $logger,
            new ReadModelEventApplier(
                $errorReporter,
                new ReadModelReflector()
            ),
            new ReadModelReflector()
        );

        $sut->setTaskProgressReporter($taskProgressReporter);

        $readModel = new ReadModel();

        $sut->pollAndApplyEvents($readModel, new InMemoryEventSequence(100, 0));

        $this->assertSame(0, $readModel->onEvent1Called);
        $this->assertSame(1, $readModel->onEvent2Called);
    }

    public function testPollFromTheBegining()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        /** @var MetaData $metadata */
        $metadata = new MetaData(
            'someId',
            'someClass',
            new \DateTimeImmutable()
        );


        /** @var EventWithMetaData[] $events */
        $events = [
            new EventWithMetaData(new Event1, $metadata->withEventId(1)->withSequence(new InMemoryEventSequence(100, 0))),
            new EventWithMetaData(new Event2, $metadata->withEventId(2)->withSequence(new InMemoryEventSequence(100, 1))),
        ];

        $eventStream = new FilteredRawEventStreamGroupedByCommit([new InMemoryEventsCommit(100, 1, $events)]);

        /** @var \Dudulina\ProgressReporting\TaskProgressReporter $taskProgressReporter */
        $taskProgressReporter = $this->getMockBuilder(TaskProgressReporter::class)
            ->getMock();

        /** @var LoggerInterface $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        /** @var ErrorReporter $errorReporter */
        $errorReporter = $this->getMockBuilder(ErrorReporter::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->with([Event1::class, Event2::class])
            ->willReturn($eventStream);

        /** @var EventStore $eventStore */
        $sut = new ReadModelRecreator(
            $eventStore,
            $logger,
            new ReadModelEventApplier(
                $errorReporter,
                new ReadModelReflector()
            ),
            new ReadModelReflector()
        );

        $sut->setTaskProgressReporter($taskProgressReporter);

        $readModel = new ReadModel();

        $sut->pollAndApplyEvents($readModel, null);

        $this->assertSame(1, $readModel->onEvent1Called);
        $this->assertSame(1, $readModel->onEvent2Called);
    }
}

class ReadModel implements ReadModelInterface
{
    public $onEvent1Called = 0;
    public $onEvent2Called = 0;

    public function clearModel()
    {
    }

    public function createModel()
    {
    }

    public function onEvent1(Event1 $event)
    {
        $this->onEvent1Called++;
        return $event;
    }

    public function onEvent2(Event2 $event)
    {
        $this->onEvent2Called++;
        throw new \Exception();
    }

    public function someOtherMethod($argument)
    {
        return $argument;
    }

    public function someOtherMethod2(\stdClass $argument)
    {
        return $argument;
    }
}

class Event1 implements Event
{

}

class Event2 implements Event
{

}
