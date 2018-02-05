<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandTester;


use Dudulina\Command;
use Dudulina\Command\CommandTester;
use Dudulina\Command\CommandValidator;

class CommandTesterWithValidator implements CommandTester
{

    /**
     * @var CommandTester
     */
    private $commandTester;
    /**
     * @var CommandValidator
     */
    private $commandValidator;

    public function __construct(
        CommandTester $commandTester,
        CommandValidator $commandValidator

    )
    {
        $this->commandTester = $commandTester;
        $this->commandValidator = $commandValidator;
    }

    public function canExecuteCommand(Command $command): bool
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            return false;
        }

        return $this->commandTester->canExecuteCommand($command);
    }
}