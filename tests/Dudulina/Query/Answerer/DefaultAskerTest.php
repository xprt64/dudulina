<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace test\Dudulina\Query\Answerer;

use Dudulina\Query\Answerer\DefaultAnswerer;
use Dudulina\Query\AskerResolver;
use Psr\Container\ContainerInterface;

class DefaultAnswererTest extends \PHPUnit_Framework_TestCase
{
    public function test_answer()
    {
        $query = new Question;

        $container  = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $asker = new Asker;
        $container->method('get')
            ->with(Asker::class)
            ->willReturn($asker);

        $askerResolver  = $this->getMockBuilder(AskerResolver::class)
            ->getMock();

        $askerResolver->method('findAskers')
            ->with($query)
            ->willReturn([new \Dudulina\Query\Answerer\MethodDescriptor(Asker::class, 'responded')]);


        $answerer = new DefaultAnswerer(
            $container,
            $askerResolver
        );

        $answerer->answer(new Question);

        $this->assertTrue($asker->called);
    }
}

class Question
{

}

class Asker
{
    public $called;

    public function responded(Question $question)
    {
        $this->called = true;
    }
}