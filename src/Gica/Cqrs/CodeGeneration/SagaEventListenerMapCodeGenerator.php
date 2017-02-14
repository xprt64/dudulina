<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;


use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ClassSorter\ByConstructorDependencySorter;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\Cqrs\Command\CodeAnalysis\WriteSideEventHandlerDetector;
use Gica\FileSystem\FileSystemInterface;
use Psr\Log\LoggerInterface;

class SagaEventListenerMapCodeGenerator implements Discoverer
{
    public function generate(
        LoggerInterface $logger,
        FileSystemInterface $fileSystem = null,
        $eventSubscriberTemplateClassName,
        string $searchDirectory,
        string $outputFilePath,
        string $outputShortClassName
    )
    {
        (new CodeGenerator())->discoverAndPutContents(
            $this,
            $fileSystem,
            $eventSubscriberTemplateClassName,
            $searchDirectory,
            $outputFilePath,
            $outputShortClassName
        );

        $logger->info("Command side (saga) events handlers map wrote to: $outputFilePath (searched in $searchDirectory)");
    }

    public function discover(string $searchDirectory)
    {
        $discoverer = new MethodListenerDiscovery(
            new WriteSideEventHandlerDetector(),
            new AnyPhpClassIsAccepted,
            new ByConstructorDependencySorter());

        $discoverer->discoverListeners($searchDirectory);

        return $discoverer->getEventToListenerMap();
    }
}