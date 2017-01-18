<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


use Gica\Types\Guid;

class MetaDataDefault implements \Gica\Cqrs\Event\MetaData
{

    /**
     * @var \DateTimeImmutable
     */
    private $dateCreated;

    /** @var \Gica\Types\Guid */
    private $aggregateId;
    private $authenticatedUserId;

    /* @var string */
    private $aggregateClass;

    public function __construct(
        \Gica\Types\Guid $aggregateId,
        string $aggregateClass,
        \DateTimeImmutable $dateCreated,
        Guid $authenticatedUserId = null
    )
    {
        $this->dateCreated = $dateCreated->setTimezone(new \DateTimeZone('Europe/Bucharest'));
        $this->aggregateId = $aggregateId;
        $this->authenticatedUserId = $authenticatedUserId;
        $this->aggregateClass = $aggregateClass;
    }

    public function getDateCreated(): \DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function getAggregateId():\Gica\Types\Guid
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
}