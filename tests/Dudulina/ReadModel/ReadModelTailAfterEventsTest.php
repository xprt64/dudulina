<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\ReadModel\ReadModelTailAfterEventsTest;


use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\InMemory\EventSequence;
use Dudulina\EventStore\InMemory\FilteredRawEventStreamGroupedByCommit;
use Dudulina\EventStore\InMemory\InMemoryEventsCommit;
use Dudulina\EventStore\TailableEventStream;
use Dudulina\ReadModel\ReadModelEventApplier;
use Dudulina\ReadModel\ReadModelEventApplier\ReadModelReflector;
use Dudulina\ReadModel\ReadModelInterface;
use Dudulina\ReadModel\ReadModelTail;
use Psr\Log\LoggerInterface;


class ReadModelTailAfterEventsTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterSomeEvent()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        /** @var MetaData $metadata */
        $metadata = new MetaData(
            'id',
            'class',
            new \DateTimeImmutable(),
            null,
            null
        );

        /** @var EventWithMetaData[] $events */
        $events = [
            new EventWithMetaData(new Event1, $metadata->withEventId(1)->withTimestamp(new EventSequence(100, 0))),
            new EventWithMetaData(new Event1, $metadata->withEventId(1)->withTimestamp(new EventSequence(100, 0))),
            new EventWithMetaData(new Event2, $metadata->withEventId(2)->withTimestamp(new EventSequence(100, 1))),
        ];

        /** @var EventWithMetaData[] $tailEvents */
        $tailEvents = [
            new EventWithMetaData(new Event3, $metadata->withEventId(3)->withTimestamp(new EventSequence(200, 0))),
            new EventWithMetaData(new Event4, $metadata->withEventId(4)->withTimestamp(new EventSequence(200, 1))),
        ];

        $eventStream = new FilteredRawEventStreamGroupedByCommit([new InMemoryEventsCommit(100, 0, $events)]);

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
            $this->factoryTail($tailEvents),
            new ReadModelEventApplier(
                $logger,
                new ReadModelReflector()
            ),
            new ReadModelReflector()
        );

        $readModel = new ReadModel();

        $sut->tailRead($readModel, new EventSequence(100, 0));

        $this->assertSame(0, $readModel->onEvent1Called);
        $this->assertSame(1, $readModel->onEvent2Called);
        $this->assertSame(1, $readModel->onEvent3Called);
        $this->assertSame(1, $readModel->onEvent4Called);
    }

    private function factoryTail($tailEvents)
    {
        return new class ($tailEvents) implements TailableEventStream
        {
            private $tailEvents;

            public function __construct($tailEvents)
            {
                $this->tailEvents = $tailEvents;
            }

            public function tail(callable $callback, $eventClasses = [], string $afterTimestamp = null): void
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
