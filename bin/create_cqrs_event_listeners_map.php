<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace cqrs;

use Dudulina\CodeGeneration\Command\CommandHandlersMapCodeGenerator;
use Dudulina\CodeGeneration\Command\CommandHandlersMapTemplate;
use Dudulina\CodeGeneration\Event\EventListenersMapCodeGenerator;
use Dudulina\CodeGeneration\Event\EventListenersMapTemplate;
use Dudulina\CodeGeneration\Lib\DirectoriesToIterator;
use Dudulina\CodeGeneration\TemplateLoader;

if (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$options = getopt('', ['src:', 'output:', 'append']);

$outputPath = $options['output'];
$append = (bool)@$options['append'];
$srcFolders = \is_array($options['src']) ? $options['src'] : [$options['src']];

$writer = new \Dudulina\CodeGeneration\CodeWriter(
    new EventListenersMapCodeGenerator(),
    EventListenersMapTemplate::class,
    'EventListenersMap'
);

$writer->writeCode($outputPath, $append, $srcFolders);
