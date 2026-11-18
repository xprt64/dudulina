<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel\ReadModelEventApplier;

use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;

class ReadModelReflectionCached extends ReadModelReflection
{
    private $methodsByEventClassCache;
    private $eventClassesCache;

    public function __construct(private ReadModelReflection $readObject)
    {
    }

    /**
     * @param string $eventClass
     * @return ListenerMethod[]
     */
    #[\Override]
    public function getMethodsByEventClass(string $eventClass)
    {
        if (!isset($this->methodsByEventClassCache[$eventClass])) {
            $this->methodsByEventClassCache[$eventClass] = $this->readObject->getMethodsByEventClass($eventClass);
        }
        return $this->methodsByEventClassCache[$eventClass];
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getEventClasses()
    {
        if (!isset($this->eventClassesCache)) {
            $this->eventClassesCache = $this->readObject->getEventClasses();
        }
        return $this->eventClassesCache;
    }
}