<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\ValueObject;


class CommandHandlerDescriptor
{

    /**
     * @var string
     */
    private $handlerClass;
    /**
     * @var string
     */
    private $methodName;

    public function __construct(
        string $handlerClass, string $methodName
    )
    {
        $this->handlerClass = $handlerClass;
        $this->methodName = $methodName;
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }
}