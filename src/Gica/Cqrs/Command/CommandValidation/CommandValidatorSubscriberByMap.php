<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CommandValidation;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandValidation;
use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

abstract class CommandValidatorSubscriberByMap implements CommandValidation\CommandValidatorSubscriber
{
    /**
     * @inheritdoc
     */
    public function getHandlersForCommand(Command $command)
    {
        $definitions = $this->getCommandHandlersDefinitions();

        if (!isset($definitions[get_class($command)])) {
            return [];
        }

        $handlersForCommand = $definitions[get_class($command)];

        $result = [];

        foreach ($handlersForCommand as $commandDefinition) {

            list($aggregateClass, $methodName) = $commandDefinition;

            $result[] = new CommandHandlerDescriptor($aggregateClass, $methodName);
        }

        return $result;
    }

    abstract protected function getCommandHandlersDefinitions(): array;
}