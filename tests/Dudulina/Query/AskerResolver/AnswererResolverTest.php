<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace test\Dudulina\Query\AskerResolver;

class AnswererResolverTest extends \PHPUnit\Framework\TestCase
{
    public function test_answer()
    {
        $answerer = new \Dudulina\Query\AskerResolver\ByMap(
            [
                Question::class => [
                    [Answerer::class, 'whenAsked'],
                    [Answerer2::class, 'whenAsked'],
                ],
            ]
        );

        $response = $answerer->findAskers(new Question);
        $this->assertCount(2,$response);

        $this->assertInstanceOf(\Dudulina\Query\Answerer\MethodDescriptor::class, $response[0]);
        $this->assertInstanceOf(\Dudulina\Query\Answerer\MethodDescriptor::class, $response[1]);

        $this->assertSame(Answerer::class,  $response[0]->getHandlerClass());
        $this->assertSame(Answerer2::class,  $response[1]->getHandlerClass());

        $asker = new Answerer();
        $response = $answerer->findAskerNotifyMethod($asker, new Question);
        $this->assertInstanceOf(\Dudulina\Query\Answerer\MethodDescriptor::class, $response);
        $this->assertSame('whenAsked', $response->getMethodName());
        $this->assertSame(Answerer::class, $response->getHandlerClass());

        $this->expectException(\InvalidArgumentException::class);

        $answerer->findAskerNotifyMethod($asker, new \stdClass());
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
class Answerer2
{
    public function whenAsked(Question $question)
    {

    }
}