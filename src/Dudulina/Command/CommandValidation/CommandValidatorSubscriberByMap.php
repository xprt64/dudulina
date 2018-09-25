<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\CommandValidation;


use Dudulina\Command;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;

class CommandValidatorSubscriberByMap implements CommandValidatorSubscriber
{
    /**
     * @var array
     */
    private $map;

    public function __construct(
        array $map
    )
    {
        $this->map = $map;
    }

    /**
     * @inheritdoc
     */
    public function getHandlersForCommand(Command $command)
    {
        $definitions = $this->map;
        if (!isset($definitions[\get_class($command)])) {
            return [];
        }
        $handlersForCommand = $definitions[\get_class($command)];
        $result = [];
        foreach ($handlersForCommand as list($aggregateClass, $methodName)) {
            $result[] = new CommandHandlerDescriptor($aggregateClass, $methodName);
        }
        return $result;
    }
}