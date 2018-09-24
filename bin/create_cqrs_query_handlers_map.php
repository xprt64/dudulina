<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace cqrs;

use Dudulina\CodeGeneration\Query\QueryHandlersMapCodeGenerator;
use Dudulina\CodeGeneration\Query\QueryHandlersMapTemplate;

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
    new QueryHandlersMapCodeGenerator(),
    QueryHandlersMapTemplate::class,
    'QueryHandlersMap'
);

$writer->writeCode($outputPath, $append, $srcFolders);
