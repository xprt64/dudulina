<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga\SagaRunner;


use Gica\Cqrs\Event\EventWithMetaData;

class EventProcessingHasStalled extends \Exception
{

    /**
     * @var EventWithMetaData
     */
    private $eventWithMetadata;

    public function __construct(EventWithMetaData $eventWithMetadata)
    {
        parent::__construct("EventProcessingHasStalled," . print_r($eventWithMetadata,1));
        $this->eventWithMetadata = $eventWithMetadata;
    }

    public function getEventWithMetadata(): EventWithMetaData
    {
        return $this->eventWithMetadata;
    }
}