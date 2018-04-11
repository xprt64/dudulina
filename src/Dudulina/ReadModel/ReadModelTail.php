<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel;

use Dudulina\Event\EventWithMetaData;
use Dudulina\EventStore;
use Dudulina\EventStore\TailableEventStream;
use Dudulina\ReadModel\ReadModelEventApplier\ReadModelReflector;
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
    /**
     * @var ReadModelEventApplier
     */
    private $readModelEventApplier;
    /**
     * @var ReadModelReflector
     */
    private $readModelReflector;

    public function __construct(
        EventStore $eventStore,
        LoggerInterface $logger,
        TailableEventStream $tailableEventStream,
        ReadModelEventApplier $readModelEventApplier,
        ReadModelReflector $readModelReflector
    )
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
        $this->tailableEventStream = $tailableEventStream;
        $this->readModelEventApplier = $readModelEventApplier;
        $this->readModelReflector = $readModelReflector;
    }

    public function tailRead(ReadModelInterface $readModel, string $after = null)
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

        $this->tailableEventStream->tail(function (EventWithMetaData $eventWithMetadata) use ($readModel) {
            $this->readModelEventApplier->applyEventOnlyOnce($readModel, $eventWithMetadata);
        }, $eventClasses, $lastSequence);
    }

}