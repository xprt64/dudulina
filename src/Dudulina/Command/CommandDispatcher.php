<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command;


use Dudulina\Command;
use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher\SideEffects;

interface CommandDispatcher
{
    public function dispatchCommand(Command $command, array $metadata = null): SideEffects;
}