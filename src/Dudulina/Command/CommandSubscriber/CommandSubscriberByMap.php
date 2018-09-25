<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\CommandSubscriber;


use Dudulina\Command;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\Exception\CommandHandlerNotFound;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;

class CommandSubscriberByMap implements CommandSubscriber
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
     * @param Command $command
     * @return CommandHandlerDescriptor
     * @throws CommandHandlerNotFound
     */
    public function getHandlerForCommand(Command $command)
    {
        $definitions = $this->map;
        if (isset($definitions[\get_class($command)])) {
            $handlersForCommand = $definitions[\get_class($command)];
            if ($handlersForCommand) {
                return new CommandHandlerDescriptor($handlersForCommand[0][0], $handlersForCommand[0][1]);
            }
        }
        throw new CommandHandlerNotFound(sprintf('A handler for command %s was not found', get_class($command)));
    }
}