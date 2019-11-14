<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Command;

use Dudulina\CodeGeneration\Command\AggregateCommandHandlerDetector;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapGrouper\GrouperByEvent;

class CommandHandlersMapCodeGenerator implements \Dudulina\CodeGeneration\CodeGenerator
{
    public function generateClass(string $template, \Iterator $filesToSearchForHandlers): string
    {
        $map = $this->getListenerDiscovery()->discoverListeners($filesToSearchForHandlers);
        $this->validateMap($this->groupMap($map));
        return $this->getCodeGenerator()->generateAndGetFileContents($map, $template);
    }

    private function getListenerDiscovery(): MethodListenerDiscovery
    {
        return new MethodListenerDiscovery(
            new AggregateCommandHandlerDetector(),
            new AnyPhpClassIsAccepted);
    }

    private function validateMap(array $map)
    {
        foreach ($map as $command => $commandHandlers) {
            if (\count($commandHandlers) > 1) {
                throw new \InvalidArgumentException(
                    sprintf('multiple handlers exists for command %s', $command));
            }
        }
    }

    private function groupMap(array $map)
    {
        return (new GrouperByEvent())->groupMap($map);
    }

    private function getCodeGenerator(): MapCodeGenerator
    {
        return new GroupedByEventMapCodeGenerator();
    }
}