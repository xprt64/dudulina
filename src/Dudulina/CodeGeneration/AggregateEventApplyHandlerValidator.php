<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration;

use Gica\CodeAnalysis\AggregateEventHandlersValidator;
use Dudulina\CodeGeneration\Aggregate\ListenerClassValidator\OnlyAggregateByName;
use Psr\Log\LoggerInterface;

class AggregateEventApplyHandlerValidator
{
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }

    public function validate(
        \Iterator $files
    )
    {
        $validator = new AggregateEventHandlersValidator(
            new OnlyAggregateByName());

        $validator->validateEventHandlers($files);

        if ($this->logger) {
            $this->logger->info("Aggregate event handlers are valid");
        }
    }
}