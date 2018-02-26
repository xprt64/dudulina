<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventStore\InMemory;


use Dudulina\Event\EventWithMetaData;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;

class InMemoryEventsCommit
{

    /**
     * @var int
     */
    private $sequence;
    /**
     * @var int
     */
    private $version;
    /**
     * @var EventWithMetaData[]
     */
    private $eventsWithMetadata;

    public function __construct(
        int $sequence,
        int $version,
        array $eventsWithMetadata
    )
    {
        $this->sequence = $sequence;
        $this->version = $version;
        $this->eventsWithMetadata = $eventsWithMetadata;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return EventWithMetaData[]
     */
    public function getEventsWithMetadata()
    {
        return $this->eventsWithMetadata;
    }

    /**
     * @inheritdoc
     */
    public function filterEventsByClass(array $eventClasses)
    {
        $other = clone $this;
        $other->eventsWithMetadata = array_filter($this->eventsWithMetadata, function (EventWithMetaData $eventWithMetaData) use ($eventClasses) {
            return $this->eventHasAnyOfThisClasses($eventWithMetaData->getEvent(), $eventClasses);
        });
        return $other;
    }

    private function eventHasAnyOfThisClasses($event, array $eventClasses)
    {
        foreach ($eventClasses as $eventClass) {

            if ((new SubclassComparator())->isASubClassOrSameClass($event, $eventClass)) {
                return true;
            }
        }

        return false;
    }
}