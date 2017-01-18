<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel\Traits;


use Gica\Cqrs\Event\MetaData;
use Gica\Types\Guid;
use MongoDB\BSON\ObjectID;

trait ObjectIdUtils
{
    private function getObjectIDFromAggregateIdMetaData(MetaData $metaData):ObjectID
    {
        return new ObjectID((string)$metaData->getAggregateId());
    }

    private function guidToObjectID(Guid $guid):ObjectID
    {
        return new ObjectID((string)$guid);
    }

}