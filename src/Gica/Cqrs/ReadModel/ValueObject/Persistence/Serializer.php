<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel\ValueObject\Persistence;


use Gica\MongoDB\Lib\DateConverter;
use Gica\Types\Enum;
use Gica\Types\Guid;
use Gica\Types\Set;
use MongoDB\BSON\ObjectID;

class Serializer
{
    public function serialize($thing)
    {
        try {
            return $this->serializeStandardObject($thing);
        } catch (\InvalidArgumentException $exception) {
            return $this->serializeCustomObject($thing);
        }
    }

    private function serializeCustomObject($object)
    {
        $reflectionClass = new \ReflectionClass($object);

        $properties = $reflectionClass->getProperties();

        $result = [];

        foreach ($properties as $i => $reflectionProperty) {
            $result[$reflectionProperty->getName()] = $this->serializeCustomObject($reflectionProperty->getValue($object));
        }

        return $result;
    }

    private function serializeStandardObject($object, $maxRecursion = 3)
    {
        if (is_scalar($object) || $maxRecursion < 0) {
            return $object;
        }

        if (is_object($object)) {

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

            throw new \InvalidArgumentException();
        }

        if (is_array($object)) {
            return array_map(function ($item) {
                return $this->serialize($item);
            }, $object);
        }

        return $object;
    }
}