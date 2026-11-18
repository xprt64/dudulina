<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\CommandValidation;


use Dudulina\Command;
use Dudulina\Command\CommandValidator;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;
use Psr\Container\ContainerInterface;

class CommandValidatorBySubscriber implements CommandValidator
{

    public function __construct(private CommandValidatorSubscriber $commandValidatorSubscriber, private ContainerInterface $container)
    {
    }

    #[\Override]
    public function validateCommand(Command $command)
    {
        $handlerDescriptors = $this->commandValidatorSubscriber->getHandlersForCommand($command);
        $errors = [[]];
        /** @var CommandHandlerDescriptor $handlerDescriptor */
        foreach ($handlerDescriptors as $handlerDescriptor) {
            $handler = $this->container->get($handlerDescriptor->getHandlerClass());
            $generator = call_user_func([$handler, $handlerDescriptor->getMethodName()], $command);
            $errors[] = iterator_to_array($generator, false);
        }
        return array_merge(...$errors);
    }
}