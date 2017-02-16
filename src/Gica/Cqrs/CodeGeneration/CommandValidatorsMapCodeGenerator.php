<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ClassSorter\ByConstructorDependencySorter;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;
use Gica\Cqrs\Command\CodeAnalysis\AggregateCommandValidatorDetector;
use Gica\FileSystem\FileSystemInterface;
use Psr\Log\LoggerInterface;

class CommandValidatorsMapCodeGenerator
{
    public function generate(
        LoggerInterface $logger,
        FileSystemInterface $fileSystem = null,
        string $commandValidatorSubscriberTemplateClassName,
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
            $commandValidatorSubscriberTemplateClassName,
            $outputFilePath,
            $outputShortClassName
        );

        $logger->info("Commands validators wrote to: $outputFilePath");
    }

    private function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new AggregateCommandValidatorDetector(),
            new AnyPhpClassIsAccepted,
            new ByConstructorDependencySorter());

        return $discoverer->discoverListeners($searchDirectory);
    }
}