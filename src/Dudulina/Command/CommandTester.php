<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command;


use Dudulina\Command;

interface CommandTester
{
     public function canExecuteCommand(Command $command): bool;
}