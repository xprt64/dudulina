<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\CommandValidation;


use Dudulina\Command;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;

class CommandValidatorSubscriberByMap implements CommandValidatorSubscriber
{
    public function __construct(private array $map)
    {
    }

    /**
     * @inheritdoc
     */
    public function getHandlersForCommand(Command $command)
    {
        $definitions = $this->map;
        if (!isset($definitions[$command::class])) {
            return [];
        }
        $handlersForCommand = $definitions[$command::class];
        $result = [];
        foreach ($handlersForCommand as [$aggregateClass, $methodName]) {
            $result[] = new CommandHandlerDescriptor($aggregateClass, $methodName);
        }
        return $result;
    }
}