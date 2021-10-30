<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration\Command;


use Dudulina\Attributes\EventProcessor;
use Dudulina\CodeGeneration\AttributeDetector;
use Dudulina\Event;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;

class WriteSideEventHandlerDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return (new SubclassComparator())->isASubClassButNoSameClass($typeHintedClass->name, Event::class);
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        if (AttributeDetector::hasAttribute($reflectionMethod, EventProcessor::class)) {
            return true;
        }
        return 0 === stripos($reflectionMethod->name, 'process') || false !== stripos($reflectionMethod->getDocComment(), '@EventProcessor');
    }
}