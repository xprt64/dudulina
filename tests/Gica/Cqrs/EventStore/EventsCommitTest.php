<?php


namespace tests\Gica\Cqrs\EventStore;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\EventStore\EventsCommit;


class EventsCommitTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $sut = new EventsCommit(
            1,
            2,
            [
                $this->wrapEventInMetadata(new MyEvent()),
                $this->wrapEventInMetadata(new MyEvent2()),
            ]
        );

        $this->assertSame(1, $sut->getSequence());
        $this->assertSame(2, $sut->getVersion());

        $sut2 = $sut->filterEventsByClass([MyEvent::class]);

        $this->assertInstanceOf(EventsCommit::class, $sut2);

        $this->assertCount(1, $sut2->getEventsWithMetadata());
        $this->assertInstanceOf(MyEvent::class, $sut2->getEventsWithMetadata()[0]->getEvent());

    }

    private function wrapEventInMetadata($event): EventWithMetaData
    {
        /** @var MetaData $metaData */

        $metaData = $this->getMockBuilder(MetaData::class)->disableOriginalConstructor()->getMock();
        return new EventWithMetaData(
            $event,
            $metaData
        );
    }

}

class MyEvent implements Event
{

}

class MyEvent2 implements Event
{

}
