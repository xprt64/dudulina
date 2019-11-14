<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration\Aggregate\ListenerClassValidator;


use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator;

class OnlyAggregateByName implements ListenerClassValidator
{
    public function isClassAccepted(\ReflectionClass $typeHintedClass):bool
    {
        return preg_match( '#Aggregate$#', $typeHintedClass->name);
    }
}