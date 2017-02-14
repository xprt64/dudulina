<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel\ValueObject;


use Gica\Iterator\IteratorTransformer\IteratorMapper;
use Gica\Types\SerializableInterface;

class ListSerializer
{
    /**
     * @param SerializableInterface[] $objectList
     * @return array
     */
    public static function serializeList($objectList)
    {
        $objectList = $objectList ?: [];

        $iteratorMapper = new IteratorMapper(function ($object) {
            /** @var SerializableInterface $object */
            return $object->serialize();
        });

        return iterator_to_array($iteratorMapper($objectList));
    }

    public static function deserializeList(string $objectClass, $serializedObjectList = null)
    {
        if ($serializedObjectList instanceof \Iterator) {
            $serializedObjectList = iterator_to_array($serializedObjectList);
        }

        if (!$serializedObjectList) {
            return [];
        }

        $serializedObjectList = array_filter($serializedObjectList, function ($objectArray) use ($serializedObjectList) {
            return $objectArray;
        });

        return array_map(function ($objectArray) use ($objectClass) {

            return call_user_func([$objectClass, 'deserialize'], $objectArray);
        }, $serializedObjectList);
    }
}