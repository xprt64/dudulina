<?php


namespace Dudulina\EventStore;


use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;
use Dudulina\Event\EventWithMetaData;

interface EventsCommit
{

    public function getVersion(): int;

    /**
     * @return EventWithMetaData[]
     */
    public function getEventsWithMetadata();

    /**
     * @param string[] $eventClasses
     * @return static
     */
    public function filterEventsByClass(array $eventClasses);
}