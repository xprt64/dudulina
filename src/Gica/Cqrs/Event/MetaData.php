<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


interface MetaData
{
    public function getDateCreated(): \DateTimeImmutable;

    public function getAggregateId():\Gica\Types\Guid;

    public function getAuthenticatedUserId();

    public function getAggregateClass();
}