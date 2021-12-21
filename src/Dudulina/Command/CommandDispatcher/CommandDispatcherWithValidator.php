<?php


namespace Dudulina\Command\CommandDispatcher;


use Dudulina\Command;
use Dudulina\Command\CommandDispatcher;
use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher\SideEffects;
use Dudulina\Command\CommandValidator;
use Dudulina\Command\Exception\CommandValidationFailed;

class CommandDispatcherWithValidator implements CommandDispatcher
{

    /**
     * @var CommandDispatcher
     */
    private $commandDispatcher;
    /**
     * @var CommandValidator
     */
    private $commandValidator;

    public function __construct(
        CommandDispatcher $commandDispatcher,
        CommandValidator $commandValidator

    )
    {
        $this->commandDispatcher = $commandDispatcher;
        $this->commandValidator = $commandValidator;
    }

    public function dispatchCommand(Command $command, array $metadata = null): SideEffects
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            throw new CommandValidationFailed($errors);
        }

        return $this->commandDispatcher->dispatchCommand($command, $metadata);
    }
}