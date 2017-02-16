<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\Shared\ClassSorter\ByConstructorDependencySorter;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByListenerMapCodeGenerator;
use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\Cqrs\ReadModel\ListenerClassValidator\OnlyReadModels;
use Gica\FileSystem\FileSystemInterface;
use Psr\Log\LoggerInterface;

class ReadModelsMapCodeGenerator
{
    public function generate(
        LoggerInterface $logger,
        FileSystemInterface $fileSystem = null,
        string $templateClassName,
        string $searchDirectory,
        string $outputFilePath,
        string $outputShortClassName
    )
    {
        $generator = new CodeGenerator(
            new GroupedByListenerMapCodeGenerator(),
            $fileSystem
        );

        $generator->discoverAndPutContents(
            $this->discover($searchDirectory),
            $templateClassName,
            $outputFilePath,
            $outputShortClassName
        );

        $logger->info("Read models map wrote to: $outputFilePath (searched in $searchDirectory)");
    }

    private function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new OnlyReadModels(),
            new ByConstructorDependencySorter()
        );

        return $discoverer->discoverListeners($searchDirectory);
    }
}