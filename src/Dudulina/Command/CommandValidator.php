<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command;


use Dudulina\Command;
use Dudulina\Command\CommandValidation\CommandValidatorSubscriber;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;
use Psr\Container\ContainerInterface;

class CommandValidator
{

    /**
     * @var CommandValidatorSubscriber
     */
    private $commandValidatorSubscriber;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        CommandValidatorSubscriber $subscriber,
        ContainerInterface $container
    )
    {
        $this->commandValidatorSubscriber = $subscriber;
        $this->container = $container;
    }

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