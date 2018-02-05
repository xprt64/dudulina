<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command;

use Dudulina\Command;
use Dudulina\Command\Exception\CommandHandlerNotFound;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;

interface CommandSubscriber
{
    /**
     * @param Command $command
     * @return CommandHandlerDescriptor
     * @throws CommandHandlerNotFound
     */
    public function getHandlerForCommand(Command $command);
}