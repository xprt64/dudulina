<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);

namespace Dudulina\Query\Asker;

use Dudulina\Query\AnswererResolver;
use Dudulina\Query\Asker;
use Dudulina\Query\AskerResolver;
use Psr\Container\ContainerInterface;

class DefaultAsker implements Asker
{

    /**
     * @var AnswererResolver
     */
    private $answererResolver;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Dudulina\Query\AskerResolver
     */
    private $askerResolver;

    public function __construct(
        ContainerInterface $container,
        AnswererResolver $answererResolver,
        AskerResolver $askerResolver
    )
    {
        $this->answererResolver = $answererResolver;
        $this->container = $container;
        $this->askerResolver = $askerResolver;
    }

    public function askAndReturn($question)
    {
        return $this->answerQuestion($question);
    }

    public function askAndNotifyAsker($question, $asker): void
    {
        $answeredQuestion = $this->answerQuestion($question);
        $method = $this->askerResolver->findAskerNotifyMethod($asker, $question);
        $asker->{$method->getMethodName()}($answeredQuestion);
    }

    private function answerQuestion($question)
    {
        $answerer = $this->answererResolver->findAnswerer($question);
        $answeredQuestion = $this->container->get($answerer->getHandlerClass())->{$answerer->getMethodName()}($question);
        return $answeredQuestion;
    }
}