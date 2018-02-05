<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\ReadModel;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;
use Dudulina\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore;
use Dudulina\ProgressReporting\TaskProgressCalculator;
use Dudulina\ProgressReporting\TaskProgressReporter;
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
    /**
     * @var \Dudulina\ProgressReporting\TaskProgressReporter|null
     */
    private $taskProgressReporter;

    public function __construct(
        EventStore $eventStore,
        LoggerInterface $logger
    )
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
    }

    public function setTaskProgressReporter(?TaskProgressReporter $taskProgressReporter)
    {
        $this->taskProgressReporter = $taskProgressReporter;
    }

    public function recreateRead(ReadModelInterface $readModel)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new AnyPhpClassIsAccepted()
        );

        $allMethods = $discoverer->findListenerMethodsInClass(get_class($readModel));

        $eventClasses = $this->getEventClassesFromMethods($allMethods);

        $this->logger->info(print_r($eventClasses, true));
        $this->logger->info("loading events...");

        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);

        $this->logger->info("applying events...");

        $taskProgress = null;

        if ($this->taskProgressReporter) {
            $taskProgress = new TaskProgressCalculator($allEvents->countCommits());
        }

        foreach ($allEvents->fetchCommits() as $eventsCommit) {

            $eventsCommit = $eventsCommit->filterEventsByClass($eventClasses);

            foreach ($eventsCommit->getEventsWithMetadata() as $eventWithMetadata) {
                /** @var EventWithMetaData $eventWithMetadata */
                $methods = $this->findMethodsByEventClass(get_class($eventWithMetadata->getEvent()), $allMethods);

                foreach ($methods as $method) {
                    call_user_func([$readModel, $method->getMethodName()], $eventWithMetadata->getEvent(), $eventWithMetadata->getMetaData());
                }
            }

            if ($this->taskProgressReporter) {
                $taskProgress->increment();
                $this->taskProgressReporter->reportProgressUpdate($taskProgress->getStep(), $taskProgress->getTotalSteps(), $taskProgress->calculateSpeed(), $taskProgress->calculateEta());
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