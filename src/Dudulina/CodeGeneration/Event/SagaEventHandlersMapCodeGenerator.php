<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Event;

use Dudulina\CodeGeneration\Command\WriteSideEventHandlerWithGroupDetector;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;

class SagaEventHandlersMapCodeGenerator implements \Dudulina\CodeGeneration\CodeGenerator
{

    public function __construct(
        private ?string $processGroup = null

    )
    {
    }

    public function generateClass(string $template, \Iterator $filesToSearchForHandlers): string
    {
        $map = $this->getListenerDiscovery()->discoverListeners($filesToSearchForHandlers);
        return $this->getCodeGenerator()->generateAndGetFileContents($map, $template);
    }

    private function getListenerDiscovery(): MethodListenerDiscovery
    {
        return new MethodListenerDiscovery(
            new WriteSideEventHandlerWithGroupDetector($this->processGroup),
            new AnyPhpClassIsAccepted);
    }

    private function getCodeGenerator(): MapCodeGenerator
    {
        return new GroupedByEventMapCodeGenerator();
    }
}