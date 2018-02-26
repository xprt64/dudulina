<?php


namespace tests\Dudulina\EventStore\Debug;


use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\Debug\DumpEventsByClass;
use Dudulina\EventStore\EventStream;
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

        $eventStream = $this->getMockBuilder(EventStream::class)
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
