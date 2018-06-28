<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventSubscriber;


use Dudulina\Event\EventSubscriber;
use Psr\Container\ContainerInterface;

abstract class EventSubscriberByMap implements EventSubscriber
{
    abstract protected function getMap(): array;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    /**
     * @param array $listenerDescriptor
     * @return callable
     */
    private function createListenerByMethod(array $listenerDescriptor)
    {
        return [$this->container->get($listenerDescriptor[0]), $listenerDescriptor[1]];
    }

    /**
     * @param array $listenersDescriptor
     * @return callable[]
     */
    private function createListenersByMethod(array $listenersDescriptor)
    {
        $result = [];

        foreach ($listenersDescriptor as $listenerDescriptor) {
            $result[] = $this->createListenerByMethod($listenerDescriptor);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getListenersForEvent($event)
    {
        $eventClass = get_class($event);

        if (isset($this->getMap()[$eventClass])) {
            return $this->createListenersByMethod($this->getMap()[$eventClass]);
        }

        return [];
    }
}