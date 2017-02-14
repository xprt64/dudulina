<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel;


use Gica\Cqrs\Event\EventsApplier\EventsApplierOnListener;
use Gica\Cqrs\EventStore;
use Psr\Log\LoggerInterface;

class ReadModelRecreator
{

    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var EventsApplierOnListener
     */
    private $eventsApplierOnListener;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EventStore $eventStore,
        EventsApplierOnListener $eventsApplierOnListener,
        LoggerInterface $logger
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplierOnListener = $eventsApplierOnListener;
        $this->logger = $logger;
    }

    public function recreateRead(ReadModelInterface $readModel)
    {
        $eventClasses = $this->getListenerEventClasses(get_class($readModel));

        $this->logger->info(print_r($eventClasses, true));
        $this->logger->info("loading events...");
        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);

        $this->logger->info("applying events...");

        $this->eventsApplierOnListener->applyEventsOnListener($readModel, $allEvents);
    }

    private function getListenerEventClasses(string $readModelClass)
    {
        $result = [];

        $classReflection = new \ReflectionClass($readModelClass);

        foreach ($classReflection->getMethods() as $reflectionMethod) {
            $eventClass = $this->tryToExtractEventClassFromMethod($reflectionMethod);

            if (false !== $eventClass) {
                $result[] = $eventClass;
            }
        }

        return $result;
    }

    private function tryToExtractEventClassFromMethod(\ReflectionMethod $reflectionMethod)
    {
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $eventClass = $this->tryToExtractEventClassFromParameter($reflectionParameter);

            if (false !== $eventClass) {
                return $eventClass;
            }
        }

        return false;
    }

    private function tryToExtractEventClassFromParameter(\ReflectionParameter $reflectionParameter)
    {
        if ($reflectionParameter->getClass() && is_subclass_of($reflectionParameter->getClass()->getName(), \Gica\Cqrs\Event::class)) {
            return $reflectionParameter->getClass()->getName();
        }

        return false;
    }
}