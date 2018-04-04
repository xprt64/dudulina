<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\ReadModelTailTest;


use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\InMemory\FilteredRawEventStreamGroupedByCommit;
use Dudulina\EventStore\InMemory\InMemoryEventsCommit;
use Dudulina\ProgressReporting\TaskProgressReporter;
use Dudulina\ReadModel\ReadModelInterface;
use Dudulina\ReadModel\ReadModelRecreator;
use Dudulina\ReadModel\ReadModelTail;
use Psr\Log\LoggerInterface;


class ReadModelTailTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        /** @var MetaData $metadata */
        $metadata = $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();


        /** @var EventWithMetaData[] $events */
        $events = [
            new EventWithMetaData(new Event1, $metadata),
            new EventWithMetaData(new Event2, $metadata),
        ];

        /** @var EventWithMetaData[] $tailEvents */
        $tailEvents = [
            new EventWithMetaData(new Event3, $metadata),
            new EventWithMetaData(new Event4, $metadata),
        ];

        $eventStream = new FilteredRawEventStreamGroupedByCommit([new InMemoryEventsCommit(1, 1, $events)]);

        /** @var \Dudulina\ProgressReporting\TaskProgressReporter $taskProgressReporter */
        $taskProgressReporter = $this->getMockBuilder(TaskProgressReporter::class)
            ->getMock();

        /** @var LoggerInterface $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->with([Event1::class, Event2::class, Event3::class, Event4::class])
            ->willReturn($eventStream);

        /** @var EventStore $eventStore */

        $sut = new ReadModelTail(
            $eventStore,
            $logger,
            $this->factoryTail($tailEvents)
        );

        $readModel = new ReadModel();

        $sut->tailRead($readModel, "someTimestamp");

        $this->assertSame(1, $readModel->onEvent1Called);
        $this->assertSame(1, $readModel->onEvent2Called);
        $this->assertSame(1, $readModel->onEvent3Called);
        $this->assertSame(1, $readModel->onEvent4Called);
    }

    private function factoryTail($tailEvents)
    {
        return new class ($tailEvents) implements \Dudulina\EventStore\TailableEventStore
        {
            private $tailEvents;

            public function __construct($tailEvents)
            {
                $this->tailEvents = $tailEvents;
            }

            /**
             * @param callable $callback function(EventWithMetadata)
             * @param string[] $eventClasses
             * @param mixed|null $afterTimestamp
             */
            public function tail(callable $callback, $eventClasses = [], $afterTimestamp = null): void
            {
                foreach ($this->tailEvents as $event) {
                    $callback($event);
                }
            }
        };
    }
}

class ReadModel implements ReadModelInterface
{
    public $onEvent1Called = 0;
    public $onEvent2Called = 0;
    public $onEvent3Called = 0;
    public $onEvent4Called = 0;

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
        return $event;
    }

    public function onEvent3(Event3 $event)
    {
        $this->onEvent3Called++;
        return $event;
    }

    public function onEvent4(Event4 $event)
    {
        $this->onEvent4Called++;
        throw new \Exception();//testing how it reacts to exceptions
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

class Event3 implements Event
{

}

class Event4 implements Event
{

}
