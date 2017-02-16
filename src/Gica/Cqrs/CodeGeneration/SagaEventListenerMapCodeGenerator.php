<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\Shared\ClassSorter\ByConstructorDependencySorter;
use Gica\Cqrs\CodeGeneration\Traits\GroupedByEventTrait;
use Gica\Cqrs\Command\CodeAnalysis\WriteSideEventHandlerDetector;

class SagaEventListenerMapCodeGenerator
{
    use GroupedByEventTrait;

    protected function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new WriteSideEventHandlerDetector(),
            new AnyPhpClassIsAccepted,
            new ByConstructorDependencySorter());

        return $discoverer->discoverListeners($searchDirectory);
    }

    protected function log($outputFilePath, $searchDirectory)
    {
        $this->logger->info("Command side (saga) events handlers map wrote to: $outputFilePath (searched in $searchDirectory)");
    }
}