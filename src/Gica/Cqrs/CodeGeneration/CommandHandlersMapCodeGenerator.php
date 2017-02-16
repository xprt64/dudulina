<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;

use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapGrouper\GrouperByEvent;
use Gica\CodeAnalysis\Shared\ClassSorter\ByConstructorDependencySorter;
use Gica\Cqrs\CodeGeneration\Traits\GroupedByEventTrait;
use Gica\Cqrs\Command\CodeAnalysis\AggregateCommandHandlerDetector;

class CommandHandlersMapCodeGenerator
{
    use GroupedByEventTrait;

    protected function log($outputFilePath, $searchDirectory)
    {
        $this->logger->info("Commands map wrote to: $outputFilePath (searched in $searchDirectory)");
    }

    private function validateMap(array $map)
    {
        foreach ($map as $command => $commandHandlers) {
            if (count($commandHandlers) > 1) {
                throw new \Exception(
                    sprintf("multiple handlers exists for command %s", $command));
            }
        }
    }

    protected function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new AggregateCommandHandlerDetector(),
            new AnyPhpClassIsAccepted,
            new ByConstructorDependencySorter());

        $map = $discoverer->discoverListeners($searchDirectory);

        $this->validateMap($this->groupMap($map));

        return $map;
    }

    private function groupMap(array $map)
    {
        return (new GrouperByEvent())->groupMap($map);
    }
}