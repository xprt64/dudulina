<?php


namespace tests\Gica\Cqrs\Event\EventSubscriber;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventSubscriber\EventSubscriberByMap;
use Gica\Dependency\ConstructorAbstractFactory;
use Interop\Container\ContainerInterface;


class EventSubscriberByMapTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $sut = new ConcreteEventSubscriberByMap(
            new ConstructorAbstractFactory($this->mockContainer())
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

class ConcreteEventSubscriberByMap extends EventSubscriberByMap
{

    protected function getMap(): array
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