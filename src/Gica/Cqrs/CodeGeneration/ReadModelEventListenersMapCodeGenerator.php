<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\Cqrs\CodeGeneration\Traits\GroupedByEventTrait;
use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;

class ReadModelEventListenersMapCodeGenerator
{
    use GroupedByEventTrait;

    protected function discover(\Iterator $files)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new AnyPhpClassIsAccepted
        );

        return $discoverer->discoverListeners($files);
    }

    protected function log($outputFilePath)
    {
        $this->logger->info("Read models events handlers map wrote to: $outputFilePath");
    }
}