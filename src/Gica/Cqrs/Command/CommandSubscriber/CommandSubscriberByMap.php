<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CommandSubscriber;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandSubscriber;
use Gica\Cqrs\Command\Exception\CommandHandlerNotFound;
use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

abstract class CommandSubscriberByMap implements CommandSubscriber
{
    /**
     * @param Command $command
     * @return CommandHandlerDescriptor
     * @throws CommandHandlerNotFound
     */
    public function getHandlerForCommand(Command $command)
    {
        $definitions = $this->getCommandHandlersDefinitions();

        if (isset($definitions[get_class($command)])) {
            $handlersForCommand = $definitions[get_class($command)];

            if ($handlersForCommand) {
                foreach ($handlersForCommand as $commandDefinition) {

                    list($aggregateClass, $methodName) = $commandDefinition;

                    return new CommandHandlerDescriptor($aggregateClass, $methodName);
                }
            }
        }

        throw new CommandHandlerNotFound(sprintf("A handler for command %s was not found", get_class($command)));
    }

    abstract protected function getCommandHandlersDefinitions(): array;
}