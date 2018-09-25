<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Event\EventSubscriber;

use Dudulina\Event;
use Dudulina\Event\EventSubscriber\EventSubscriberByMap;
use Psr\Container\ContainerInterface;


class EventSubscriberByMapTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $sut = new EventSubscriberByMap(
            $this->mockContainer(),
            (new ConcreteEventSubscriberByMap)->getMap()
        );

        $this->assertCount(2, $sut->getListenersForEvent(new Event1));

        $this->assertCount(0, $sut->getListenersForEvent(new Event2));
    }

    private function mockContainer()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        return $container;
    }
}

class ConcreteEventSubscriberByMap
{

    public function getMap(): array
    {
        return [
            Event1::class => [
                [Listener1::class, 'onEvent1'],
                [Listener2::class, 'onEvent1'],
            ],
        ];
    }
}

class Event1 implements Event
{

}

class Event2 implements Event
{

}

class Listener1
{
    public function onEvent1(Event1 $event)
    {

    }
}

class Listener2
{
    public function onEvent1(Event1 $event)
    {

    }
}