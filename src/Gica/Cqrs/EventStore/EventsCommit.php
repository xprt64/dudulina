<?php


namespace Gica\Cqrs\EventStore;


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
}