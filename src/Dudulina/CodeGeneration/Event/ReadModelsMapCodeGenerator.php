<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Event;

use Dudulina\CodeGeneration\Command\ReadModelEventHandlerDetector;
use Dudulina\ReadModel\ListenerClassValidator\OnlyReadModels;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByListenerMapCodeGenerator;

class ReadModelsMapCodeGenerator implements \Dudulina\CodeGeneration\CodeGenerator
{
    public function generateClass(string $template, \Iterator $filesToSearchForHandlers): string
    {
        $map = $this->getListenerDiscovery()->discoverListeners($filesToSearchForHandlers);
        return $this->getCodeGenerator()->generateAndGetFileContents($map, $template);
    }

    private function getListenerDiscovery(): MethodListenerDiscovery
    {
        return new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new OnlyReadModels());
    }

    private function getCodeGenerator(): MapCodeGenerator
    {
        return new GroupedByListenerMapCodeGenerator();
    }
}