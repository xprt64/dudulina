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
    private $commitSequence;
    /**
     * @var int
     */
    private $version;
    /**
     * @var EventWithMetaData[]
     */
    private $eventsWithMetadata;

    public function __construct(
        int $commitSequence,
        int $version,
        array $eventsWithMetadata
    )
    {
        $this->commitSequence = $commitSequence;
        $this->version = $version;
        $this->eventsWithMetadata = $eventsWithMetadata;
    }

    public function getCommitSequence(): int
    {
        return $this->commitSequence;
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