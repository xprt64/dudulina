<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\Query\QueryAskerDetector;
use Dudulina\CodeGeneration\Traits\GroupedByEventTrait;
use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;

class QueryAskersMapCodeGenerator
{
    use GroupedByEventTrait;

    protected function discover(\Iterator $files)
    {
        $discoverer = new MethodListenerDiscovery(
            new QueryAskerDetector(),
            new AnyPhpClassIsAccepted
        );

        return $discoverer->discoverListeners($files);
    }

    protected function log($outputFilePath)
    {
        $this->logger->info("Query askers map wrote to: $outputFilePath");
    }
}