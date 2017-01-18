<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Event\EventsApplierOnListener;
use Gica\Cqrs\EventStore;
use Gica\Cqrs\EventStore\EventStream;
use Gica\Dependency\AbstractFactory;

/**
 * Saga este un proces care proceseaza evenimente de la mai multe agregate modificandu-si starea
 * si in anumite conditii in functie de stare, genereaza comenzi
 * E o problema ca procesul ar trebui sa ruleze continuu pentru ca la restartare ar fi rehidratat cu multe evenimente
 * @todo
 */
class SagaRepositoryDefault implements \Gica\Cqrs\Saga\SagaRepository
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
     * @var EventStream[]
     */
    private $aggregateToEventStreamMap;
    /**
     * @var AbstractFactory
     */
    private $abstractFactory;

    public function __construct(
        AbstractFactory $abstractFactory,
        EventStore $eventStore,
        EventsApplierOnListener $eventsApplierOnListener
    )
    {
        $this->eventStore = $eventStore;
        $this->eventsApplierOnListener = $eventsApplierOnListener;
        $this->aggregateToEventStreamMap = new \SplObjectStorage();
        $this->abstractFactory = $abstractFactory;
    }

    public function loadSaga(string $sagaClass)
    {
        $saga = $this->abstractFactory->createObject($sagaClass);

        $eventClasses = $this->getListenerEventClasses($sagaClass);

//        print_r($eventClasses);
//        echo "loading events...\n";
        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);
//        echo "applying events...\n";

        $this->eventsApplierOnListener->applyEventsOnListener($saga, $allEvents);
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