<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\Shared\ClassSorter\ByConstructorDependencySorter;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;
use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\FileSystem\FileSystemInterface;
use Psr\Log\LoggerInterface;

class ReadModelEventListenersMapCodeGenerator
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
            new GroupedByEventMapCodeGenerator(),
            $fileSystem
        );

        $generator->discoverAndPutContents(
            $this->discover($searchDirectory),
            $templateClassName,
            $outputFilePath,
            $outputShortClassName
        );

        $logger->info("Read models events handlers map wrote to: $outputFilePath");
    }

    private function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new ReadModelEventHandlerDetector(),
            new AnyPhpClassIsAccepted,
            new ByConstructorDependencySorter()
        );

        return $discoverer->discoverListeners($searchDirectory);
    }
}