<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CommandSubscriber;


use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

abstract class CommandSubscriberByMap implements \Gica\Cqrs\Command\CommandSubscriber
{
    /**
     * @param \Gica\Cqrs\Command $command
     * @return CommandHandlerDescriptor
     * @throws \Gica\Cqrs\Command\Exception\CommandHandlerNotFound
     */
    public function getHandlerForCommand(\Gica\Cqrs\Command $command)
    {
        $commandHandlersDefinitions = $this->getCommandHandlersDefinitions();

        $handlersForCommand = $commandHandlersDefinitions[get_class($command)];

        if($handlersForCommand)
        {
            foreach ($handlersForCommand as $commandDefinition) {

                list($aggregateClass, $methodName) = $commandDefinition;

                return new CommandHandlerDescriptor($aggregateClass, $methodName);
            }
        }

        throw new \Gica\Cqrs\Command\Exception\CommandHandlerNotFound(sprintf("A handler for command %s was not found", get_class($command)));
    }

    abstract protected function getCommandHandlersDefinitions():array;
}