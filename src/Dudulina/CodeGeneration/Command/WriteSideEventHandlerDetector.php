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
    /**
     * @param bool $attributeOnly when true only methods marked with #[EventProcessor] are accepted;
     *        the method-name and docblock conventions are ignored (default: they are accepted too)
     */
    public function __construct(private bool $attributeOnly = false)
    {
    }

    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return (new SubclassComparator())->isASubClassButNoSameClass($typeHintedClass->name, Event::class);
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        if (AttributeDetector::hasAttribute($reflectionMethod, EventProcessor::class)) {
            return true;
        }

        if ($this->attributeOnly) {
            return false;
        }
        return 0 === stripos($reflectionMethod->name, 'process') || false !== stripos($reflectionMethod->getDocComment(), '@EventProcessor');
    }
}