<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace test\Dudulina\Query\Asker;

use Dudulina\Query\Asker\DefaultAsker;
use Dudulina\Query\AskerResolver;
use Psr\Container\ContainerInterface;

class DefaultAskerTest extends \PHPUnit\Framework\TestCase
{
    public function test_answer()
    {
        $question = new Question;
        $answerer = new Answerer;
        $asker = new Asker;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $container->method('get')
            ->with(Answerer::class)
            ->willReturn($answerer);

        $askerResolver = $this->getMockBuilder(AskerResolver::class)
            ->getMock();

        $askerResolver->method('findAskers')
            ->with($question)
            ->willReturn([new \Dudulina\Query\Answerer\MethodDescriptor(Asker::class, 'responded')]);

        $askerResolver->method('findAskerNotifyMethod')
            ->with($asker, $question)
            ->willReturn(new \Dudulina\Query\Answerer\MethodDescriptor(Asker::class, 'responded'));

        $answererResolver = $this->getMockBuilder(\Dudulina\Query\AnswererResolver::class)
            ->getMock();

        $answererResolver->method('findAnswerer')
            ->with($question)
            ->willReturn(new \Dudulina\Query\Answerer\MethodDescriptor(Answerer::class, 'whenAsked'));


        $answerer = new DefaultAsker(
            $container,
            $answererResolver,
            $askerResolver
        );

        $response = $answerer->askAndReturn($question);

        $this->assertSame($question, $response);

        $answerer->askAndNotifyAsker($question, $asker);
        $this->assertTrue($asker->called);
    }
}

class Question
{

}

class Answerer
{
    public function whenAsked(Question $question)
    {
        return $question;
    }
}


class Asker
{
    public $called;

    public function responded(Question $question)
    {
        $this->called = true;
    }
}