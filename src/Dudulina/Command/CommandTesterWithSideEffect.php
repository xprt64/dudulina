<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command;


use Dudulina\Command;

interface CommandTesterWithSideEffect
{
     public function shouldExecuteCommand(Command $command): bool;
}