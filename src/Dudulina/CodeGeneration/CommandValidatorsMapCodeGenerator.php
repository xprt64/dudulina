<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Dudulina\CodeGeneration\Traits\GroupedByEventTrait;
use Dudulina\Command\CodeAnalysis\AggregateCommandValidatorDetector;

class CommandValidatorsMapCodeGenerator
{
    use GroupedByEventTrait;

    protected function discover(\Iterator $files)
    {
        $discoverer = new MethodListenerDiscovery(
            new AggregateCommandValidatorDetector(),
            new AnyPhpClassIsAccepted);

        return $discoverer->discoverListeners($files);
    }

    protected function log($outputFilePath)
    {
        $this->logger->info("Commands validators wrote to: $outputFilePath");
    }
}