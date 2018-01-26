<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command;

class CommandWithMetadata
{

    /**
     * @var Command
     */
    private $command;
    private $metadata;

    public function __construct(
        Command $command,
        CommandMetadata $metadata = null
    )
    {
        $this->command = $command;
        $this->metadata = $metadata;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getMetadata():?CommandMetadata
    {
        return $this->metadata;
    }

    public function getAggregateId()
    {
        return $this->getCommand()->getAggregateId();
    }
}