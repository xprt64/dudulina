<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace test\Dudulina\Query\AnswererResolver;

class AnswererResolverTest extends \PHPUnit\Framework\TestCase
{
    public function test_answer()
    {
        $answerer = new \Dudulina\Query\AnswererResolver\ByMap(
            [
                Question::class => [
                    [Answerer::class, 'whenAsked'],
                ],
            ]
        );

        $response = $answerer->findAnswerer(new Question);

        $this->assertInstanceOf(\Dudulina\Query\Answerer\MethodDescriptor::class, $response);
        $this->assertSame(Answerer::class, $response->getHandlerClass());

        $this->expectException(\InvalidArgumentException::class);
        $answerer->findAnswerer(new \stdClass());
    }
}

class Question
{

}

class Answerer
{
    public function whenAsked(Question $question)
    {

    }
}