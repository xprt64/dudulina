<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel\ValueObject;


use Gica\MongoDB\Lib\DateConverter;
use Gica\Types\Enum;
use Gica\Types\Guid;
use Gica\Types\Set;
use Gica\Types\SerializableInterface;
use MongoDB\BSON\ObjectID;

class ObjectSerializer
{
    public function serializeObject($object, $maxRecursion = 3)
    {
        if (is_scalar($object) || $maxRecursion < 0) {
            return $object;
        }

        if (is_object($object)) {
            if ($object instanceof SerializableInterface) {
                return $this->serializeObject($object->serialize(), $maxRecursion - 1);
            }

            if ($object instanceof Guid) {
                return new ObjectID($object);
            }

            if ($object instanceof Enum) {
                return $object->toPrimitive();
            }

            if ($object instanceof Set) {
                return $object->toPrimitive();
            }

            if ($object instanceof \DateTimeInterface) {
                return DateConverter::UTCDateTime($object);
            }
            return 'unserializable ' . get_class($object);
        }

        if (is_array($object)) {
            return array_map(function ($item) {
                return $this->serializeObject($item);
            }, $object);
        }

        return $object;
    }
}