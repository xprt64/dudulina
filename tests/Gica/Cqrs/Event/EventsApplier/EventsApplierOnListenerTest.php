<?php


namespace tests\Gica\Cqrs\Event\EventsApplier;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventsApplier\EventsApplierOnListener;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\Event\MetaData;


class EventsApplierOnListenerTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        /** @var Event $event */
        $event = $this->getMockBuilder(Event::class)->getMock();
        /** @var MetaData $metaData */
        $metaData = $this->getMockBuilder(MetaData::class)->disableOriginalConstructor()->getMock();

        $eventsWithMetaData = new EventWithMetaData(
            $event,
            $metaData
        );

        $sut = new EventsApplierOnListener();

        $listener = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['on' . get_class($event)])
            ->getMock();

        $listener->expects($this->once())
            ->method('on' . get_class($event))
            ->with($eventsWithMetaData->getEvent(), $eventsWithMetaData->getMetaData());

        $sut->applyEventsOnListener($listener, [$eventsWithMetaData]);
    }
}
