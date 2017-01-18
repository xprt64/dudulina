<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command;

interface CommandDispatcher
{
    public function dispatchCommand(Command $command);

    public function canExecuteCommand(Command $command) : bool;
}