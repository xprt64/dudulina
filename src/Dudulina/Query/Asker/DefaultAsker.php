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

    public function __construct(private ContainerInterface $container, private AnswererResolver $answererResolver, private AskerResolver $askerResolver)
    {
    }

    #[\Override]
    public function askAndReturn($question)
    {
        return $this->answerQuestion($question);
    }

    #[\Override]
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