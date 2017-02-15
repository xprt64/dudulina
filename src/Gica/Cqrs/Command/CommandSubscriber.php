<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;

use Gica\Cqrs\Command;
use Gica\Cqrs\Command\Exception\CommandHandlerNotFound;
use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

interface CommandSubscriber
{
    /**
     * @param Command $command
     * @return CommandHandlerDescriptor
     * @throws CommandHandlerNotFound
     */
    public function getHandlerForCommand(Command $command);
}