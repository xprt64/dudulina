<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;
use Gica\CodeAnalysis\Shared\ClassSorter\ByConstructorDependencySorter;
use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Cqrs\EventStore;
use Psr\Log\LoggerInterface;

class ReadModelRecreator
{

    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EventStore $eventStore,
        LoggerInterface $logger
    )
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
    }

    public function recreateRead(ReadModelInterface $readModel)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new AnyPhpClassIsAccepted(),
            new ByConstructorDependencySorter()
        );

        $allMethods = $discoverer->findListenerMethodsInClass(get_class($readModel));

        $eventClasses = $this->getEventClassesFromMethods($allMethods);

        $this->logger->info(print_r($eventClasses, true));
        $this->logger->info("loading events...");

        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);

        $this->logger->info("applying events...");

        foreach ($allEvents as $eventWithMetadata) {
            /** @var EventWithMetaData $eventWithMetadata */
            $methods = $this->findMethodsByEventClass(get_class($eventWithMetadata->getEvent()), $allMethods);

            foreach ($methods as $method) {

                call_user_func([$readModel, $method->getMethodName()], $eventWithMetadata->getEvent(), $eventWithMetadata->getMetaData());
            }
        }
    }

    /**
     * @param ListenerMethod[] $methods
     * @return array
     */
    private function getEventClassesFromMethods($methods)
    {
        $eventClasses = [];
        foreach ($methods as $listenerMethod) {
            $eventClasses[] = $listenerMethod->getEventClassName();
        }

        return $eventClasses;
    }

    /**
     * @param string $eventClass
     * @param ListenerMethod[] $allMethods
     * @return ListenerMethod[]
     */
    private function findMethodsByEventClass(string $eventClass, $allMethods)
    {
        $result = [];

        foreach ($allMethods as $listenerMethod) {
            if ($listenerMethod->getEventClassName() == $eventClass) {
                $result[] = $listenerMethod;
            }
        }

        return $result;
    }
}