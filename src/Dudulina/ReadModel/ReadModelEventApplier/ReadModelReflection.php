<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel\ReadModelEventApplier;

use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;

class ReadModelReflection
{
    /** @var ListenerMethod[] */
    private $listenerMethods = [];

    /**
     * @param ListenerMethod[] $listenerMethods
     */
    public function __construct(array $listenerMethods)
    {
        $this->listenerMethods = $listenerMethods;
    }

    /**
     * @param string $eventClass
     * @return ListenerMethod[]
     */
    public function getMethodsByEventClass(string $eventClass)
    {
        return array_filter($this->listenerMethods, function (ListenerMethod $listenerMethod) use ($eventClass) {
            return $listenerMethod->getEventClassName() === $eventClass;
        });
    }

    /**
     * @return string[]
     */
    public function getEventClasses()
    {
        return array_unique(array_reduce($this->listenerMethods, function ($acc, ListenerMethod $listenerMethod) {
            $acc[] = $listenerMethod->getEventClassName();
            return $acc;
        }, []));
    }
}