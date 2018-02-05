<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\CommandValidation;

use Dudulina\Command;
use Dudulina\Command\Exception\CommandHandlerNotFound;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;

interface CommandValidatorSubscriber
{
    /**
     * @param Command $command
     * @return CommandHandlerDescriptor[]
     * @throws CommandHandlerNotFound
     */
    public function getHandlersForCommand(Command $command);
}