<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command;


use Dudulina\Command;

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