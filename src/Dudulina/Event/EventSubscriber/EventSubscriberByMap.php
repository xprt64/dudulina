<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventSubscriber;


use Dudulina\Event\EventSubscriber;
use Psr\Container\ContainerInterface;

class EventSubscriberByMap implements EventSubscriber
{

    /** @var ContainerInterface */
    private $container;
    /**
     * @var array
     */
    private $map;

    public function __construct(
        ContainerInterface $container,
        array $map
    )
    {
        $this->container = $container;
        $this->map = $map;
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
        $eventClass = \get_class($event);
        if (isset($this->map[$eventClass])) {
            return $this->createListenersByMethod($this->map[$eventClass]);
        }
        return [];
    }
}