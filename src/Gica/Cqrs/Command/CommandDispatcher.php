<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command;

interface CommandDispatcher
{
    public function dispatchCommand(Command $command, CommandMetadata $metadata = null);
}