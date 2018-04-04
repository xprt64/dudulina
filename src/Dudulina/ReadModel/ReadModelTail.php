<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel;


use Dudulina\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\SeekableEventStream;
use Dudulina\EventStore\TailableEventStream;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;
use Psr\Log\LoggerInterface;

class ReadModelTail
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
     * @var TailableEventStream
     */
    private $tailableEventStream;

    public function __construct(
        EventStore $eventStore,
        LoggerInterface $logger,
        TailableEventStream $tailableEventStream
    )
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
        $this->tailableEventStream = $tailableEventStream;
    }

    public function tailRead(ReadModelInterface $readModel, $after = null)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new AnyPhpClassIsAccepted()
        );

        $allMethods = $discoverer->findListenerMethodsInClass(\get_class($readModel));

        $eventClasses = $this->getEventClassesFromMethods($allMethods);

        $this->logger->info(print_r($eventClasses, true));
        $this->logger->info('loading events...');

        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);

        if ($after && $allEvents instanceof SeekableEventStream) {
            $allEvents->afterTimestamp($after);
        }

        $this->logger->info('applying events...');

        $lastTimestamp = null;

        foreach ($allEvents as $eventWithMetadata) {
            /** @var EventWithMetaData $eventWithMetadata */
            $methods = $this->findMethodsByEventClass(\get_class($eventWithMetadata->getEvent()), $allMethods);
            foreach ($methods as $method) {
                $this->executeMethod($readModel, $method, $eventWithMetadata);
            }
            $lastTimestamp = $eventWithMetadata->getMetaData()->getTimestamp();
        }

        $this->logger->info('tailing events...');

        $this->tailableEventStream->tail(function (EventWithMetaData $eventWithMetadata) use ($readModel, $allMethods) {
            $methods = $this->findMethodsByEventClass(\get_class($eventWithMetadata->getEvent()), $allMethods);
            foreach ($methods as $method) {
                $this->executeMethod($readModel, $method, $eventWithMetadata);
            }
        }, $eventClasses, $lastTimestamp);
    }

    /**
     * @param ListenerMethod[] $methods
     * @return string[]
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
            if ($listenerMethod->getEventClassName() === $eventClass) {
                $result[] = $listenerMethod;
            }
        }

        return $result;
    }

    private function executeMethod(ReadModelInterface $readModel, ListenerMethod $method, EventWithMetaData $eventWithMetadata): void
    {
        try {
            $readModel->{$method->getMethodName()}($eventWithMetadata->getEvent(), $eventWithMetadata->getMetaData());
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'model'          => \get_class($readModel),
                'eventId'        => $eventWithMetadata->getMetaData()->getEventId(),
                'aggregateId'    => $eventWithMetadata->getMetaData()->getAggregateId(),
                'aggregateClass' => $eventWithMetadata->getMetaData()->getAggregateClass(),
                'file'           => $exception->getFile(),
                'line'           => $exception->getLine(),
            ]);
        }
    }
}