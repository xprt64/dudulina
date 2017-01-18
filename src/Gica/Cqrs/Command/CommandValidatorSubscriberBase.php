<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

abstract class CommandValidatorSubscriberBase implements \Gica\Cqrs\CommandValidatorSubscriber
{
    /**
     * @inheritdoc
     */
    public function getHandlersForCommand(\Gica\Cqrs\Command $command)
    {
        $commandHandlersDefinitions = $this->getCommandHandlersDefinitions();

        $handlersForCommand = $commandHandlersDefinitions[get_class($command)];

        $result = [];

        if (!$handlersForCommand) {
            return $result;
        }

        foreach ($handlersForCommand as $commandDefinition) {

            list($aggregateClass, $methodName) = $commandDefinition;

            $result[] = new CommandHandlerDescriptor($aggregateClass, $methodName);
        }

        return $result;
    }

    abstract protected function getCommandHandlersDefinitions():array;
}