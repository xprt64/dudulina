<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;

use Gica\CodeAnalysis\MethodListenerDiscovery;
use Gica\CodeAnalysis\MethodListenerDiscovery\ClassSorter\ByConstructorDependencySorter;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator\AnyPhpClassIsAccepted;
use Gica\CodeAnalysis\MethodListenerDiscovery\MethodListenerMapperWriter;
use Gica\Cqrs\Command\CodeAnalysis\AggregateCommandHandlerDetector;
use Psr\Log\LoggerInterface;

class CommandHandlersMapCodeGenerator
{
    public function generate(
        LoggerInterface $logger,
        string $commandSubscriberTemplateClassName,
        string $searchDirectory,
        string $outputFilePath,
        string $outputShortClassName = 'CommandHandlerSubscriber')
    {
        $classInfo = new \ReflectionClass($commandSubscriberTemplateClassName);

        $classInfo->getShortName();

        @unlink($outputFilePath);

        $discoverer = new MethodListenerDiscovery(
            new AggregateCommandHandlerDetector(),
            new AnyPhpClassIsAccepted,
            new ByConstructorDependencySorter());

        $discoverer->discoverListeners($searchDirectory);

        $map = $discoverer->getEventToListenerMap();

        $this->validateMap($map);

        $writer = new MethodListenerMapperWriter();

        $template = file_get_contents($classInfo->getFileName());

        $template = str_replace($classInfo->getShortName() /*CommandSubscriberTemplate*/, $outputShortClassName /*CommandHandlerSubscriber*/, $template);

        $template = str_replace('--- This is just a template ---', '--- generated by ' . __FILE__ . ' at ' . date('c') . ' ---', $template);

        $code = $writer->generateAndGetFileContents($map, $template);

        if (false === file_put_contents($outputFilePath, $code))
            $logger->error("write error");

        chmod($outputFilePath, 0777);

        $logger->info("Commands map wrote to: $outputFilePath (searched in $searchDirectory)");

    }

    private function validateMap(array $map)
    {
        foreach ($map as $command => $commandHandlers) {
            if (count($commandHandlers) > 1) {
                throw new \Exception(sprintf("multiple handlers exists for command %s", $command));
            }
        }
    }
}