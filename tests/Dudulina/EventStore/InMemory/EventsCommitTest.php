<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Testing\EventStore\InMemory;


use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventsCommit;


class EventsCommitTest extends \PHPUnit\Framework\TestCase
{

    public function test()
    {
        $sut = new InMemoryEventsCommit(
            1,
            2,
            [
                $this->wrapEventInMetadata(new MyEvent()),
                $this->wrapEventInMetadata(new MyEvent2()),
            ]
        );

        $this->assertSame(1, $sut->getCommitSequence());
        $this->assertSame(2, $sut->getVersion());

        $sut2 = $sut->filterEventsByClass([MyEvent::class]);

        $this->assertInstanceOf(InMemoryEventsCommit::class, $sut2);

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
