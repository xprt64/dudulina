<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event;


class ObjectToJsonConverter
{
    public function serializeObject($object)
    {
        $result = $this->extractObjectProperties($object);

        return $result;
    }

    private function extractObjectProperties($obj)
    {
        if (!is_object($obj)) {
            return $obj;
        }

        $class = new \ReflectionClass($obj);

        $properties = $class->getProperties();

        $result = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($obj);

            if (is_object($value)) {
                if (is_callable([$value, '__toString'])) {
                    $value = (string)$value;
                } else {

                }
            } else if (is_array($value)) {
                $value = array_map(function ($item) {
                    return $this->extractObjectProperties($item);
                }, $value);
            }

            $result[$property->getName()] = $value;
        }

        return $result;
    }

}