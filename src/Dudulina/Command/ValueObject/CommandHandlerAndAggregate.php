<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\ValueObject;


class CommandHandlerAndAggregate
{

    public function __construct(private CommandHandlerDescriptor $commandHandler, private $aggregate)
    {
    }

    public function getCommandHandler(): CommandHandlerDescriptor
    {
        return $this->commandHandler;
    }

    public function getAggregate()
    {
        return $this->aggregate;
    }
}