<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CodeAnalysis;


use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\Cqrs\Command;

class AggregateCommandValidatorDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass):bool
    {
        return is_subclass_of($typeHintedClass->name, Command::class) &&
        $typeHintedClass->name != Command::class;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod):bool
    {
        return 0 === stripos($reflectionMethod->name, 'validate');
    }
}