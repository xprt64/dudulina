<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\CodeGeneration;

use Gica\CodeAnalysis\AggregateEventHandlersValidator;
use Gica\Cqrs\Aggregate\CodeAnalysis\ListenerClassValidator\OnlyAggregateByName;
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
        string $searchDirectory
    )
    {
        $validator = new AggregateEventHandlersValidator(
            new OnlyAggregateByName());

        $validator->validateEventHandlers($searchDirectory);

        if ($this->logger) {
            $this->logger->info("Aggregate event handlers are valid in $searchDirectory");
        }
    }
}