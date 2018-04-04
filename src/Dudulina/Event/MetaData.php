<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event;


use Dudulina\Command\CommandMetadata;

class MetaData
{

    /**
     * @var \DateTimeImmutable
     */
    private $dateCreated;

    private $aggregateId;
    private $authenticatedUserId;

    /* @var string */
    private $aggregateClass;

    /** @var CommandMetadata|null */
    private $commandMetadata;

    /** @var int */
    private $version = null;

    /**
     * The sequence/timestamp. It is always increasing
     * @var mixed
     */
    private $ts = null;

    /**
     * @var string|null
     */
    private $eventId;

    public function __construct(
        $aggregateId,
        string $aggregateClass,
        \DateTimeImmutable $dateCreated,
        $authenticatedUserId = null,
        CommandMetadata $commandMetadata = null
    )
    {
        $this->dateCreated = $this->addTimeZone($dateCreated);
        $this->aggregateId = $aggregateId;
        $this->authenticatedUserId = $authenticatedUserId;
        $this->aggregateClass = $aggregateClass;
        $this->commandMetadata = $commandMetadata;
    }

    public function getDateCreated(): \DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    public function getAggregateClass()
    {
        return $this->aggregateClass;
    }

    public function getAuthenticatedUserId()
    {
        return $this->authenticatedUserId;
    }

    public function getCommandMetadata():?CommandMetadata
    {
        return $this->commandMetadata;
    }

    private function addTimeZone(\DateTimeImmutable $dateCreated): \DateTimeImmutable
    {
        return $dateCreated->getTimezone() ? $dateCreated :
            ($dateCreated->setTimezone(new \DateTimeZone('Europe/Bucharest')) ?: $dateCreated);
    }

    public function getTimestamp()
    {
        return $this->ts;
    }

    public function withEventId(string $eventId): self
    {
        $other = clone $this;
        $other->eventId = $eventId;
        return $other;
    }

    public function withTimestamp($ts): self
    {
        $other = clone $this;
        $other->ts = $ts;
        return $other;
    }

    public function withVersion(int $version): self
    {
        $other = clone $this;
        $other->version = $version;
        return $other;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getEventId(): ?string
    {
        return $this->eventId;
    }
}