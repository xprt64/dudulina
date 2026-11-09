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
    /**
     * @param bool $attributeOnly when true only methods marked with #[QueryHandler] are accepted;
     *        the method-name and docblock conventions are ignored (default: they are accepted too)
     */
    public function __construct(private bool $attributeOnly = false)
    {
    }

    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return true;
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        if (AttributeDetector::hasAttribute($reflectionMethod, QueryHandler::class)) {
            return true;
        }

        if ($this->attributeOnly) {
            return false;
        }
        return 0 === stripos($reflectionMethod->name, 'whenAsked') ||
               false !== stripos($reflectionMethod->getDocComment(), '@QueryHandler');
    }
}