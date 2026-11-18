<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration\Event;


use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;
use Dudulina\Event;

class EventClassDetector implements MessageClassDetector
{
    #[\Override]
    public function isMessageClass(\ReflectionClass $typeHintedClass):bool
    {
        return new SubclassComparator()->isASubClassButNoSameClass($typeHintedClass->name, Event::class);
    }

    #[\Override]
    public function isMethodAccepted(\ReflectionMethod $reflectionMethod):bool
    {
        return true;
    }
}