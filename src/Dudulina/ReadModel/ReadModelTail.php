<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel;

use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\EventSequence;
use Dudulina\EventStore\TailableEventStream;
use Dudulina\ReadModel\ReadModelEventApplier\ReadModelReflector;
use Dudulina\ReadModel\ReadModelTail\EventProcessedNotifier;
use Psr\Log\LoggerInterface;

class ReadModelTail
{
    public function __construct(private EventStore $eventStore, private LoggerInterface $logger, private TailableEventStream $tailableEventStream, private ReadModelEventApplier $readModelEventApplier, private ReadModelReflector $readModelReflector)
    {
    }

    public function tailRead(ReadModelInterface $readModel, ?EventSequence $after = null, ?EventProcessedNotifier $eventProcessedNotifier = null)
    {
        $eventClasses = $this->readModelReflector->getEventClassesFromReadModel($readModel);

        $this->logger->info(print_r($eventClasses, true));
        $this->logger->info('loading events...');

        $allEvents = $this->eventStore->loadEventsByClassNames($eventClasses);

        if ($after) {
            $allEvents->afterSequence($after);
        }

        $this->logger->info('applying events...');

        $lastSequence = $after;

        foreach ($allEvents as $eventWithMetadata) {
            /** @var EventWithMetaData $eventWithMetadata */
            $this->readModelEventApplier->applyEventOnlyOnce($readModel, $eventWithMetadata);
            $lastSequence = $eventWithMetadata->getMetaData()->getSequence();
        }

        $this->logger->info('tailing events...');

        $this->tailableEventStream->tail(
            function (EventWithMetaData $eventWithMetadata) use ($readModel, $eventProcessedNotifier) {
                $this->readModelEventApplier->applyEventOnlyOnce($readModel, $eventWithMetadata);
                $eventProcessedNotifier and $eventProcessedNotifier->onEventProcessed($eventWithMetadata);
            },
            $eventClasses,
            $lastSequence
        );
    }

}