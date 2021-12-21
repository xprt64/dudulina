<?php


namespace tests\Dudulina\Event\EventSubscriber\ManualEventSubscriberTest;


use Dudulina\Event;
use Dudulina\Event\EventSubscriber\ManualEventSubscriber;


class ManualEventSubscriberTest extends \PHPUnit\Framework\TestCase
{

    public function test()
    {
        $sut = new ManualEventSubscriber();

        $listener1 = new class extends \stdClass
        {
            public function handleEvent1(Event1 $event)
            {

            }
        };

        $listener2 = new class extends \stdClass
        {
            public function handleEvent1(Event1 $event)
            {

            }
        };

        $listener3 = new class extends \stdClass
        {
            public function onEvent1(Event1 $event)
            {

            }
        };

        $sut->subscribeToEvent($listener1);
        $sut->subscribeToEvent($listener2);
        $sut->subscribeToEvent($listener3);//not accepted

        $listeners = $sut->getListenersForEvent(new Event1);

        $this->assertCount(2, $listeners);
    }
}


class Event1 implements Event
{

}