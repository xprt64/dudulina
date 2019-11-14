<?php
/**
 * Copyright (c) 2019 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel\ReadModelEventApplier;

use Dudulina\Event\EventWithMetaData;
use Throwable;

interface ErrorReporter
{
    public function reportEventApplyError($readModel, string $methodName, EventWithMetaData $eventWithMetadata, Throwable $exception):void;
}