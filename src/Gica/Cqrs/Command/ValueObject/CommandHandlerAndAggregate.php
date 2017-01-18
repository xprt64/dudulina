<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\ValueObject;


class CommandHandlerAndAggregate
{

    /**
     * @var CommandHandlerDescriptor
     */
    private $commandHandler;
    private $aggregate;

    public function __construct(
        CommandHandlerDescriptor $commandHandler,
        $aggregate
    )
    {
        $this->commandHandler = $commandHandler;
        $this->aggregate = $aggregate;
    }

    /**
     * @return CommandHandlerDescriptor
     */
    public function getCommandHandler(): CommandHandlerDescriptor
    {
        return $this->commandHandler;
    }

    public function getAggregate()
    {
        return $this->aggregate;
    }
}