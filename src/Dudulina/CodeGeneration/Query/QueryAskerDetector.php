<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Query;

use Dudulina\Attributes\QueryAsker;
use Dudulina\CodeGeneration\AttributeDetector;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;

class QueryAskerDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return true;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        if (AttributeDetector::hasAttribute($reflectionMethod, QueryAsker::class)) {
            return true;
        }
        return 0 === stripos($reflectionMethod->name, 'whenAnswered') ||
               false !== stripos($reflectionMethod->getDocComment(), '@QueryAsker');
    }
}