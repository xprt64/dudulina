<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\CodeAnalysis;


class EventClassDetector implements \Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass):bool
    {
        return is_subclass_of($typeHintedClass->getName(), \Gica\Cqrs\Event::class) && $typeHintedClass->getName() != \Gica\Cqrs\Event::class;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod):bool
    {
        return true;
    }
}