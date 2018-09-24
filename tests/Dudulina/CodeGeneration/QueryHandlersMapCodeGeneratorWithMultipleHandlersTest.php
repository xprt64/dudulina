<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration;

require_once __DIR__ . '/QueryHandlersWithMultipleHandlersMapCodeGeneratorData/Query.php';
require_once __DIR__ . '/QueryHandlersWithMultipleHandlersMapCodeGeneratorData/ReadModel1.php';
require_once __DIR__ . '/QueryHandlersWithMultipleHandlersMapCodeGeneratorData/ReadModel2.php';

class QueryHandlersMapCodeGeneratorWithMultipleHandlersTest extends \PHPUnit_Framework_TestCase
{


    public function test()
    {
        $template = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('#multiple handlers exists for query#ims');

        $sut = new \Dudulina\CodeGeneration\Query\QueryHandlersMapCodeGenerator();

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/QueryHandlersWithMultipleHandlersMapCodeGeneratorData/ReadModel1.php',
                __DIR__ . '/QueryHandlersWithMultipleHandlersMapCodeGeneratorData/ReadModel2.php',
            ])
        );
    }
}
