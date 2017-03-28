<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command;

interface CommandDispatcher
{
    public function dispatchCommand(Command $command, $metadata = null);

    public function canExecuteCommand(Command $command): bool;
}