<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */
namespace Dudulina\CodeGeneration\Query;

use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;
use Dudulina\Event;

class QueryAskerDetector implements MessageClassDetector
{
    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return true;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        return 0 === stripos($reflectionMethod->name, 'whenAnswered') ||
            false !== stripos($reflectionMethod->getDocComment(), '@QueryAsker');
    }
}