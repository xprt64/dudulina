<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration;

use Dudulina\CodeGeneration\Query\QueryHandlerDetector;
use Dudulina\CodeGeneration\Traits\GroupedByEventTrait;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapGrouper\GrouperByEvent;

class QueryHandlersMapCodeGenerator
{
    use GroupedByEventTrait;

    protected function log($outputFilePath)
    {
        $this->logger->info("Query handlers map wrote to: $outputFilePath");
    }

    private function validateMap(array $map)
    {
        foreach ($map as $command => $commandHandlers) {
            if (\count($commandHandlers) > 1) {
                throw new \Exception(
                    sprintf('multiple handlers exists for query %s', $command));
            }
        }
    }

    protected function discover(\Iterator $files)
    {
        $discoverer = new MethodListenerDiscovery(
            new QueryHandlerDetector(),
            new AnyPhpClassIsAccepted);

        $map = $discoverer->discoverListeners($files);

        $this->validateMap($this->groupMap($map));

        return $map;
    }

    private function groupMap(array $map)
    {
        return (new GrouperByEvent())->groupMap($map);
    }
}