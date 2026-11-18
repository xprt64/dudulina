<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandTester;

use Dudulina\Command;
use Dudulina\Command\CommandTesterWithExplanation;
use Dudulina\Command\CommandValidator;

class CommandTesterWithExplanationWithValidator implements CommandTesterWithExplanation
{

    public function __construct(private CommandTesterWithExplanation $commandTester, private CommandValidator $commandValidator)
    {
    }

    public function whyCantExecuteCommand(Command $command)
    {
        $unknownErrors = $this->commandValidator->validateCommand($command);
        $validatorErrors = array_map(function ($error) {
            if (!($error instanceof \Throwable)) {
                $error = new \Exception((string)$error);
            }
            return $error;
        }, $unknownErrors);
        $testerErrors = $this->commandTester->whyCantExecuteCommand($command);
        return array_merge($validatorErrors, $testerErrors);
    }
}