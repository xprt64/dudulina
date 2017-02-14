<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ClassSorter\ByConstructorDependencySorter;
use Gica\CodeAnalysis\MethodListenerDiscovery\ReadModelMapperWriter;
use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\Cqrs\ReadModel\ListenerClassValidator\OnlyReadModels;
use Gica\FileSystem\FileSystemInterface;
use Psr\Log\LoggerInterface;

class ReadModelsMapCodeGenerator implements Discoverer
{
    public function generate(
        LoggerInterface $logger,
        FileSystemInterface $fileSystem = null,
        string $readModelMapTemplateClassName,
        string $searchDirectory,
        string $outputFilePath,
        string $outputShortClassName
    )
    {
        (new CodeGenerator(
            new ReadModelMapperWriter()
        ))->discoverAndPutContents(
            $this,
            $fileSystem,
            $readModelMapTemplateClassName,
            $searchDirectory,
            $outputFilePath,
            $outputShortClassName
        );

        $logger->info("Read models map wrote to: $outputFilePath (searched in $searchDirectory)");
    }

    public function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new OnlyReadModels(),
            new ByConstructorDependencySorter()
        );

        $discoverer->discoverListeners($searchDirectory);

        return $discoverer->getAllEventsListeners();
    }
}