<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CodeAnalysis;


class AggregateCommandHandlerDetector implements \Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass):bool
    {
        return is_subclass_of($typeHintedClass->getName(), \Gica\Cqrs\Command::class) &&
        $typeHintedClass->getName() != \Gica\Cqrs\Command::class;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod):bool
    {
        return 0 === stripos($reflectionMethod->getName(), 'handle');
    }
}