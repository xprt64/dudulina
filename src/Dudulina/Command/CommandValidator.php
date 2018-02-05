<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command;


use Dudulina\Command;
use Dudulina\Command\CommandValidation\CommandValidatorSubscriber;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;
use Gica\Dependency\AbstractFactory;

class CommandValidator
{

    /**
     * @var CommandValidatorSubscriber
     */
    private $commandValidatorSubscriber;
    /**
     * @var AbstractFactory
     */
    private $abstractFactory;

    public function __construct(
        CommandValidatorSubscriber $subscriber,
        AbstractFactory $abstractFactory
    )
    {
        $this->commandValidatorSubscriber = $subscriber;
        $this->abstractFactory = $abstractFactory;
    }

    public function validateCommand(Command $command)
    {
        $handlerDescriptors = $this->commandValidatorSubscriber->getHandlersForCommand($command);

        $errors = [];

        /** @var CommandHandlerDescriptor $handlerDescriptor */
        foreach ($handlerDescriptors as $handlerDescriptor) {
            $handler = $this->abstractFactory->createObject($handlerDescriptor->getHandlerClass());

            $generator = call_user_func([$handler, $handlerDescriptor->getMethodName()], $command);

            $ownErrors = iterator_to_array($generator, false);

            $errors = array_merge($errors, $ownErrors);
        }

        return $errors;
    }
}