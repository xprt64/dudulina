<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;

use Gica\CodeAnalysis\AggregateEventHandlersValidator;
use Gica\Cqrs\Aggregate\CodeAnalysis\OnlyAggregateByName;
use Psr\Log\LoggerInterface;

class AggregateEventApplyHandlerValidator
{
    public function validate(
        LoggerInterface $logger,
        string $searchDirectory
    )
    {
        $validator = new AggregateEventHandlersValidator(
            new OnlyAggregateByName());

        $validator->validateEventHandlers($searchDirectory);

        $logger->info("Aggregate event handlers are valid in $searchDirectory");
    }
}