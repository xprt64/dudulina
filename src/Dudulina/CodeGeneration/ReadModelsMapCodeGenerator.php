<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Dudulina\CodeGeneration\Traits\GroupedByListenerTrait;
use Dudulina\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Dudulina\ReadModel\ListenerClassValidator\OnlyReadModels;

class ReadModelsMapCodeGenerator
{
    use GroupedByListenerTrait;

    protected function discover(\Iterator $files)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new OnlyReadModels()
        );

        return $discoverer->discoverListeners($files);
    }

    protected function log($outputFilePath)
    {
        $this->logger->info("Read models map wrote to: $outputFilePath");
    }
}