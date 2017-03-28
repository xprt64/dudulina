<?php


namespace Gica\Cqrs\Command\CommandDispatcher;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandDispatcher;
use Gica\Cqrs\Command\CommandValidator;
use Gica\Cqrs\Command\Exception\CommandValidationFailed;

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

    public function dispatchCommand(Command $command, $commandMetadata = null)
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            throw new CommandValidationFailed($errors);
        }

        $this->commandDispatcher->dispatchCommand($command);
    }

    public function canExecuteCommand(Command $command): bool
    {
        $errors = $this->commandValidator->validateCommand($command);

        if (!empty($errors)) {
            return false;
        }

        return $this->commandDispatcher->canExecuteCommand($command);
    }
}