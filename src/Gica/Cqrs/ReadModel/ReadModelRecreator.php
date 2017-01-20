<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel;


class ReadModelRecreator
{

    /**
     * @var \Gica\Cqrs\EventStore
     */
    private $eventStore;
    /**
     * @var \Gica\Cqrs\Event\EventsApplier\EventsApplierOnListener
     */
    private $eventsApplierOnListener;

    public function __construct(
        \Gica\Cqrs\EventStore $eventStore,
        \Gica\Cqrs\Event\EventsApplier\EventsApplierOnListener $eventsApplierOnListener
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplierOnListener = $eventsApplierOnListener;
    }

    public function recreateRead(ReadModelInterface $readModel)
    {
        $eventClasses = $this->getListenerEventClasses(get_class($readModel));

        print_r($eventClasses);
        echo "loading events...\n";
        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);
        echo "applying events...\n";

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