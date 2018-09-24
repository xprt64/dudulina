<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace cqrs;

use Dudulina\CodeGeneration\AggregateEventApplyHandlerValidator;
use Dudulina\CodeGeneration\Lib\DirectoriesToIterator;

if (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$options = getopt('', ['src:',]);

$srcFolders = \is_array($options['src']) ? $options['src'] : [$options['src']];

try {
    $aggregateEventHandlerValidator = new AggregateEventApplyHandlerValidator();
    $aggregateEventHandlerValidator->validate(new DirectoriesToIterator($srcFolders));
    exit(0);
} catch (\Throwable $exception) {
    echo $exception->getMessage();
    exit($exception->getCode());
}
