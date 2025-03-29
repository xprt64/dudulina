<?php
/******************************************************************************
 * Copyright (c) 2025 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration\Command;


use Dudulina\Attributes\EventHandler;
use Dudulina\Event;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use Gica\CodeAnalysis\Shared\ClassComparison\SubclassComparator;

class WriteSideEventHandlerWithGroupDetector implements MessageClassDetector
{

    public function __construct(
        private ?string $processGroup = null
    )
    {
    }

    public function isMessageClass(\ReflectionClass $typeHintedClass): bool
    {
        return (new SubclassComparator())->isASubClassButNoSameClass($typeHintedClass->name, Event::class);
    }

    public function isMethodAccepted(\ReflectionMethod $reflectionMethod): bool
    {
        if (version_compare(PHP_VERSION, '8', '>=')) {
            $attributes =  $reflectionMethod->getAttributes( EventHandler::class);
            if($attributes){
                foreach ($attributes as $attribute) {
                    if($attribute->getArguments()['processGroup'] === $this->processGroup){
                        return true;
                    }
                }
            }
        }

        return false;
    }
}