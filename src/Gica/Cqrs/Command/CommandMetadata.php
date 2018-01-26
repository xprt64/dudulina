<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command;


use Gica\Types\Guid;

class CommandMetadata
{
    /** @var Guid|null */
    private $commandId = null;

    /** @var Guid|null */
    private $correlationId = null;

    public function getCommandId():?Guid
    {
        return $this->commandId;
    }

    /**
     * @param Guid $commandId
     * @return static
     */
    public function withCommandId(Guid $commandId)
    {
        $other = clone $this;
        $other->commandId = $commandId;
        return $other;
    }

    /**
     * @return static
     */
    public function withoutCommandId()
    {
        $other = clone $this;
        $other->commandId = null;
        return $other;
    }

    public function getCorrelationId():?Guid
    {
        return $this->correlationId;
    }

    /**
     * @param Guid $correlationId
     * @return static
     */
    public function withCorrelationId(Guid $correlationId)
    {
        $other = clone $this;
        $other->correlationId = $correlationId;
        return $other;
    }

    /**
     * @return static
     */
    public function withoutCorrelationId()
    {
        $other = clone $this;
        $other->correlationId = null;
        return $other;
    }
}