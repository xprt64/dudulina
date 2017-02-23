<?php


namespace Gica\Cqrs\EventStore;


use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;
use Gica\Cqrs\Event\EventWithMetaData;

class EventsCommit
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

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return EventWithMetaData[]
     */
    public function getEventsWithMetadata(): array
    {
        return $this->eventsWithMetadata;
    }

    public function filterEventsByClass(array $eventClasses): self
    {
        $events = array_filter($this->eventsWithMetadata, function (EventWithMetaData $eventWithMetaData) use ($eventClasses) {
            return $this->eventHasAnyOfThisClasses($eventWithMetaData->getEvent(), $eventClasses);
        });

        return new self(
            $this->sequence,
            $this->version,
            $events
        );
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