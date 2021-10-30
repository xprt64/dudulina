<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Query;

use Dudulina\Attributes\QueryHandler;
use Dudulina\CodeGeneration\AttributeDetector;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;

class QueryHandlerDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return true;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        if (AttributeDetector::hasAttribute($reflectionMethod, QueryHandler::class)) {
            return true;
        }
        return 0 === stripos($reflectionMethod->name, 'whenAsked') ||
               false !== stripos($reflectionMethod->getDocComment(), '@QueryHandler');
    }
}