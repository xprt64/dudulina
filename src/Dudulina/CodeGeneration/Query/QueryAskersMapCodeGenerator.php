<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Query;

use Dudulina\CodeGeneration\CodeGenerator;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;

class QueryAskersMapCodeGenerator implements CodeGenerator
{
    /** @param bool $attributeOnly accept only methods marked with the attribute (ignore the name / docblock conventions) */
    public function __construct(private bool $attributeOnly = false)
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
            new QueryAskerDetector($this->attributeOnly),
            new AnyPhpClassIsAccepted);
    }

    private function getCodeGenerator(): MapCodeGenerator
    {
        return new GroupedByEventMapCodeGenerator();
    }
}