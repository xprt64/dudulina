<?php


namespace tests\Gica\Cqrs\ReadModel;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnListener;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\InMemory\RawEventStream;
use Gica\Cqrs\ReadModel\ReadModelInterface;
use Gica\Cqrs\ReadModel\ReadModelRecreator;
use Psr\Log\LoggerInterface;


class ReadModelRecreatorTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        $eventsApplierOnListener = $this->getMockBuilder(EventsApplierOnListener::class)
            ->getMock();

        $events = [
            new Event1,
            new Event2,
        ];

        $eventStream = new RawEventStream($events);

        $eventsApplierOnListener->expects($this->once())
            ->method('applyEventsOnListener')
            ->with($this->isInstanceOf(ReadModel::class), $eventStream);

        /** @var LoggerInterface $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->with([Event1::class, Event2::class])
            ->willReturn($eventStream);

        /** @var EventStore $eventStore */
        /** @var EventsApplierOnListener $eventsApplierOnListener */

        $sut = new ReadModelRecreator(
            $eventStore,
            $eventsApplierOnListener,
            $logger
        );

        $sut->recreateRead(new ReadModel());
    }
}

class ReadModel implements ReadModelInterface
{

    public function clearModel()
    {
    }

    public function createModel()
    {
    }

    public function onEvent1(Event1 $event)
    {

    }

    public function onEvent2(Event2 $event)
    {

    }

    public function someOtherMethod($argument)
    {

    }
}

class Event1 implements Event
{

}

class Event2 implements Event
{

}
