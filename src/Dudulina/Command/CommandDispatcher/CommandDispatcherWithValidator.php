<?php


namespace Dudulina\Command\CommandDispatcher;


use Dudulina\Command;
use Dudulina\Command\CommandDispatcher;
use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher\SideEffects;
use Dudulina\Command\CommandValidator;
use Dudulina\Command\Exception\CommandValidationFailed;

class CommandDispatcherWithValidator implements CommandDispatcher
{

    public function __construct(private CommandDispatcher $commandDispatcher, private CommandValidator $commandValidator)
    {
    }

    public function dispatchCommand(Command $command, ?array $metadata = null): SideEffects
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            throw new CommandValidationFailed($errors);
        }

        return $this->commandDispatcher->dispatchCommand($command, $metadata);
    }
}