<?php


namespace tests\Dudulina\EventStore\InMemory\RawEventStreamTest;


use Dudulina\Event;
use Dudulina\Event\EventWithMetaData;
use Dudulina\Event\MetaData;
use Dudulina\EventStore\InMemory\EventSequence;
use Dudulina\EventStore\InMemory\FilteredRawEventStreamGroupedByCommit;
use Dudulina\EventStore\InMemory\InMemoryEventsCommit;


class RawEventStreamTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $eventCommits = [
            new InMemoryEventsCommit(
                100,
                1,
                [1, 2]
            ),
            new InMemoryEventsCommit(
                200,
                2,
                [3, 4])
            , new InMemoryEventsCommit(
                300,
                3,
                [5, 6]),
        ];

        $sut = new FilteredRawEventStreamGroupedByCommit($eventCommits);

        $this->assertEquals([1, 2, 3, 4, 5, 6], iterator_to_array($sut->getIterator()));


        $sut = new FilteredRawEventStreamGroupedByCommit($eventCommits);
        $sut->limitCommits(2);
        $this->assertEquals([1, 2, 3, 4], iterator_to_array($sut->getIterator()));
        $this->assertEquals(4, $sut->count());
    }

    public function testWithEventClasses()
    {
        $event1 = new MyEvent();
        $event1->prop = '1';
        $event2 = new MyEvent();
        $event2->prop = '2';

        $event3 = new MyEvent2();
        $event3->prop = '3';
        $event4 = new MyEvent2();
        $event4->prop = '4';

        $eventCommits = [
            new InMemoryEventsCommit(
                100,
                1,
                [
                    $this->wrapEventInMetadata($event1),
                    $this->wrapEventInMetadata($event2),
                ]
            ),
            new InMemoryEventsCommit(
                200,
                2,
                [
                    $this->wrapEventInMetadata($event3),
                    $this->wrapEventInMetadata($event4),
                ]
            ),
        ];

        $sut = new FilteredRawEventStreamGroupedByCommit($eventCommits, [MyEvent::class]);

        $commits = $sut->fetchCommits();

        $this->assertCount(1, $commits);
        $this->assertEquals(1, $sut->countCommits());

        $this->assertEquals(100, $commits[0]->getCommitSequence());

        //$sut->afterSequence()
    }

    public function testWithSeek()
    {
        $event1 = new MyEvent();
        $event1->prop = '1';
        $event2 = new MyEvent();
        $event2->prop = '2';
        $event3 = new MyEvent2();
        $event3->prop = '3';
        $event4 = new MyEvent2();
        $event4->prop = '4';

        $eventCommits = [
            new InMemoryEventsCommit(
                100,
                1,
                [
                    $this->wrapEventInMetadata($event1, new EventSequence(100, 0)),
                    $this->wrapEventInMetadata($event2, new EventSequence(100, 1))
                ]
            ),
            new InMemoryEventsCommit(
                200,
                2,
                [
                    $this->wrapEventInMetadata($event3, new EventSequence(200, 0)),
                    $this->wrapEventInMetadata($event4, new EventSequence(200, 1))
                ]
            ),
        ];

        $sut = new FilteredRawEventStreamGroupedByCommit($eventCommits, []);
        $sut->afterSequence(new EventSequence(200, 0));//after the first event from the second commit
        $this->assertEquals(1, $sut->count());
        /** @var EventWithMetaData[] $events */
        $events = iterator_to_array($sut, false);
        $this->assertCount(1, $events);
        $this->assertSame($event4, $events[0]->getEvent());

        $sut = new FilteredRawEventStreamGroupedByCommit($eventCommits, []);
        $sut->beforeSequence(new EventSequence(100, 1));//after the first event from the second commit
        $this->assertEquals(1, $sut->count());
        /** @var EventWithMetaData[] $events */
        $events = iterator_to_array($sut, false);
        $this->assertCount(1, $events);
        $this->assertSame($event1, $events[0]->getEvent());
    }

    private function wrapEventInMetadata($event, EventSequence $eventSequence = null): EventWithMetaData
    {
        /** @var MetaData $metaData */
        $metaData = new MetaData(
            'someId',
            'someClass',
            new \DateTimeImmutable(),
            null,
            null
        );
        if ($eventSequence) {
            $metaData = $metaData->withSequence($eventSequence);
        }
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
