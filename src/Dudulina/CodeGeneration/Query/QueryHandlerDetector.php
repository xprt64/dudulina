<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Query;

use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;

class QueryHandlerDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return true;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        return 0 === stripos($reflectionMethod->name, 'whenAsked') ||
            false !== stripos($reflectionMethod->getDocComment(), '@QueryHandler');
    }
}