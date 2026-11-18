<?php
/**
 * Copyright (c) 2019 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Testing\EventStore\InMemory;


use Dudulina\Event\EventWithMetaData;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;

class InMemoryEventsCommit
{

    public function __construct(
        private int $commitSequence,
        private int $version,
        /**
         * @var EventWithMetaData[]
         */
        private array $eventsWithMetadata
    )
    {
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
        $other->eventsWithMetadata = array_filter($this->eventsWithMetadata, fn(EventWithMetaData $eventWithMetaData) => $this->eventHasAnyOfThisClasses($eventWithMetaData->getEvent(), $eventClasses));
        return $other;
    }

    private function eventHasAnyOfThisClasses($event, array $eventClasses): bool
    {
        foreach ($eventClasses as $eventClass) {

            if (new SubclassComparator()->isASubClassOrSameClass($event, $eventClass)) {
                return true;
            }
        }

        return false;
    }
}