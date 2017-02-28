<?php


namespace tests\Gica\Cqrs\EventStore\Debug;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\Debug\DumpEventsByClass;
use Gica\Cqrs\EventStore\EventStream;
use Gica\Cqrs\EventStore\EventStreamGroupedByCommit;
use Psr\Log\LoggerInterface;


class DumpEventsByClassTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        /** @var MetaData $metaData */
        $metaData = $this->getMockBuilder(MetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeast(1))
            ->method('info');

        $eventsWithMetaData1 = [
            new EventWithMetaData(
                $event1,
                $metaData
            ),
        ];

        $eventStream = $this->getMockBuilder(EventStreamGroupedByCommit::class)
            ->getMock();
        $eventStream->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($eventsWithMetaData1));

        $eventStore = $this->getMockBuilder(EventStore::class)
            ->getMock();

        $eventStore->expects($this->once())
            ->method('loadEventsByClassNames')
            ->willReturn($eventStream);

        /** @var LoggerInterface $logger */
        /** @var EventStore $eventStore */
        /** @var EventStream $eventStream */

        $sut = new DumpEventsByClass(
            $eventStore,
            $logger
        );

        $sut->dumpEvents([]);
    }
}
