<?php
/*
 * Copyright (c) 2025 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Command;

use Dudulina\Attributes\AggregateCommandValidator;
use Dudulina\CodeGeneration\AttributeDetector;
use Dudulina\Command;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;

/**
 * A command validator is a method marked with #[AggregateCommandValidator] that takes a Command.
 *
 * Unlike {@see AggregateCommandValidatorDetector}, neither the `validate*` method name nor the
 * `@AggregateCommandValidator` docblock are accepted, so a misspelled name cannot silently drop a validator
 * (which is dangerous when the validator is an authorizer). The attribute is valid on any command validator,
 * whether or not the command is handled by an aggregate.
 */
class AttributeOnlyAggregateCommandValidatorDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return (new SubclassComparator())->isASubClassButNoSameClass($typeHintedClass->name, Command::class);
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        return AttributeDetector::hasAttribute($reflectionMethod, AggregateCommandValidator::class);
    }
}
