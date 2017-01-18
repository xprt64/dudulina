<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;

class CommandValidator
{

    /**
     * @var \Gica\Cqrs\CommandValidatorSubscriber
     */
    private $commandValidatorSubscriber;
    /**
     * @var \Gica\Interfaces\Dependency\AbstractFactory
     */
    private $abstractFactory;

    public function __construct(
        \Gica\Cqrs\CommandValidatorSubscriber $commandValidatorSubscriber,
        \Gica\Interfaces\Dependency\AbstractFactory $abstractFactory
    )
    {
        $this->commandValidatorSubscriber = $commandValidatorSubscriber;
        $this->abstractFactory = $abstractFactory;
    }

    public function validateCommand(\Gica\Cqrs\Command $command)
    {
        $handlerDescriptors = $this->commandValidatorSubscriber->getHandlersForCommand($command);

        $errors = [];

        /** @var CommandHandlerDescriptor $handlerDescriptor */
        foreach ($handlerDescriptors as $handlerDescriptor) {
            $handler = $this->abstractFactory->createObject($handlerDescriptor->getHandlerClass());

            $generator = call_user_func([$handler, $handlerDescriptor->getMethodName()], $command);

            $ownErrors = iterator_to_array($generator);

            $errors = array_merge($errors, $ownErrors);
        }

        return $errors;
    }
}