<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel\ReadModelEventApplier;

use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;

class ReadModelReflectionCached extends ReadModelReflection
{
    /** @var ReadModelReflection */
    private $readObject;

    private $methodsByEventClassCache;

    public function __construct(ReadModelReflection $readObject)
    {
        $this->readObject = $readObject;
    }

    /**
     * @param string $eventClass
     * @return ListenerMethod[]
     */
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
    public function getEventClasses()
    {
        if (!isset($this->eventClassesCache)) {
            $this->eventClassesCache = $this->readObject->getEventClasses();
        }
        return $this->eventClassesCache;
    }
}