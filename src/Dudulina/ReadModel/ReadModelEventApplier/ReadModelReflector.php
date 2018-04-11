<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel\ReadModelEventApplier;

use Dudulina\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;

class ReadModelReflector
{
    /** @var ReadModelReflection[] */
    private $cache;

    public function reflectReadModel($readModel): ReadModelReflection
    {
        $readModelClass = \get_class($readModel);
        if (!isset($this->cache[$readModelClass])) {
            $this->cache[$readModelClass] = $this->findReadModelReflection($readModelClass);
        }
        return $this->cache[$readModelClass];
    }

    private function findReadModelReflection(string $readModelClass): ReadModelReflection
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new AnyPhpClassIsAccepted()
        );

        return new ReadModelReflectionCached(
            new ReadModelReflection(
                $discoverer->findListenerMethodsInClass($readModelClass)
            )
        );
    }

    /**
     * @param $readModel
     * @return string[]
     */
    public function getEventClassesFromReadModel($readModel)
    {
        return $this->reflectReadModel($readModel)->getEventClasses();
    }

    /**
     * @param $readModel
     * @param $eventClass
     * @return ListenerMethod[]
     */
    public function getListenersForEvent($readModel, $eventClass)
    {
        return $this->reflectReadModel($readModel)->getMethodsByEventClass($eventClass);
    }

}