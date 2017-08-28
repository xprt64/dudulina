<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command\CommandTester;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandTester;
use Gica\Cqrs\Command\CommandValidator;

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