<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command;


use Dudulina\Command;

interface CommandDispatcher
{
    public function dispatchCommand(Command $command, CommandMetadata $metadata = null);
}