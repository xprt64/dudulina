<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command;


use Dudulina\Command;

interface CommandTesterWithExplanation
{
    /**
     * @param Command $command
     * @return \Throwable[]
     */
     public function whyCantExecuteCommand(Command $command);
}