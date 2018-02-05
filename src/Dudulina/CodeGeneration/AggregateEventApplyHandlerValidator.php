<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\CodeGeneration;

use Gica\CodeAnalysis\AggregateEventHandlersValidator;
use Dudulina\Aggregate\CodeAnalysis\ListenerClassValidator\OnlyAggregateByName;
use Psr\Log\LoggerInterface;

class AggregateEventApplyHandlerValidator
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger = null
    )
    {
        $this->logger = $logger;
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