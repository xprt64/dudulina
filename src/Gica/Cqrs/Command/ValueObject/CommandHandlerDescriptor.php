<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\ValueObject;


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

    /**
     * @return string
     */
    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }
}