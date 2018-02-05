<?php


namespace tests\Dudulina\FutureEvents;


use Dudulina\Event\EventWithMetaData;
use Dudulina\Scheduling\ScheduledEventWithMetadata;


class ScheduledEventTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        /** @var EventWithMetaData $eventWithMetadata */
        $eventWithMetadata = $this->getMockBuilder(EventWithMetaData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new ScheduledEventWithMetadata(1, $eventWithMetadata);

        $this->assertSame(1, $sut->getEventId());
        $this->assertSame($eventWithMetadata, $sut->getEventWithMetaData());
    }
}
