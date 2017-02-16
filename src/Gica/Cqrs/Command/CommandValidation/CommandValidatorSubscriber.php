<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CommandValidation;

use Gica\Cqrs\Command;
use Gica\Cqrs\Command\Exception\CommandHandlerNotFound;
use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

interface CommandValidatorSubscriber
{
    /**
     * @param Command $command
     * @return CommandHandlerDescriptor[]
     * @throws CommandHandlerNotFound
     */
    public function getHandlersForCommand(Command $command);
}