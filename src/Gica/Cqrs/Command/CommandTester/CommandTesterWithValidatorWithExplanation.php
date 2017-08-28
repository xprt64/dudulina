<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command\CommandTester;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandTesterWithExplanation;
use Gica\Cqrs\Command\CommandValidator;

class CommandTesterWithValidatorWithExplanation implements CommandTesterWithExplanation
{

    /**
     * @var CommandTesterWithExplanation
     */
    private $commandTester;
    /**
     * @var CommandValidator
     */
    private $commandValidator;

    public function __construct(
        CommandTesterWithExplanation $commandTester,
        CommandValidator $commandValidator

    )
    {
        $this->commandTester = $commandTester;
        $this->commandValidator = $commandValidator;
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