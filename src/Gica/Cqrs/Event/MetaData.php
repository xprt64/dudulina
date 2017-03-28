<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


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
    private $commandMetadata;

    public function __construct(
        $aggregateId,
        string $aggregateClass,
        \DateTimeImmutable $dateCreated,
        $authenticatedUserId = null,
        $commandMetadata = null
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

    public function getCommandMetadata()
    {
        return $this->commandMetadata;
    }

    private function addTimeZone(\DateTimeImmutable $dateCreated): \DateTimeImmutable
    {
        return $dateCreated->getTimezone() ? $dateCreated :
            ($dateCreated->setTimezone(new \DateTimeZone('Europe/Bucharest')) ?: $dateCreated);
    }
}