<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\Shared\ClassSorter\ByConstructorDependencySorter;
use Gica\Cqrs\CodeGeneration\Traits\GroupedByListenerTrait;
use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\Cqrs\ReadModel\ListenerClassValidator\OnlyReadModels;

class ReadModelsMapCodeGenerator
{
    use GroupedByListenerTrait;

    protected function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new OnlyReadModels(),
            new ByConstructorDependencySorter()
        );

        return $discoverer->discoverListeners($searchDirectory);
    }

    protected function log($outputFilePath, $searchDirectory)
    {
        $this->logger->info("Read models map wrote to: $outputFilePath (searched in $searchDirectory)");
    }
}