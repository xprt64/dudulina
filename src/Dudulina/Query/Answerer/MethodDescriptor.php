<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);
namespace Dudulina\Query\Answerer;

class MethodDescriptor
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