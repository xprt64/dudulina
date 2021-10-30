<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration\Command;


use Dudulina\Attributes\AggregateCommandValidator;
use Dudulina\CodeGeneration\AttributeDetector;
use Dudulina\Command;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;

class AggregateCommandValidatorDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass):bool
    {
        return (new SubclassComparator())->isASubClassButNoSameClass($typeHintedClass->name, Command::class);
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod):bool
    {
        if (AttributeDetector::hasAttribute($reflectionMethod, AggregateCommandValidator::class)) {
            return true;
        }
        return 0 === stripos($reflectionMethod->name, 'validate') ||
            false !== stripos($reflectionMethod->getDocComment(), '@AggregateCommandValidator');
    }
}