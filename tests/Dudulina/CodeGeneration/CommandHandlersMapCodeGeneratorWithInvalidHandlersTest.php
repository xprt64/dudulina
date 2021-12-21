<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorWithInvalidHandlersTest;

class CommandHandlersMapCodeGeneratorWithInvalidHandlersTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $sut = new \Dudulina\CodeGeneration\Command\CommandHandlersMapCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('#multiple handlers exists for command#ims');

        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Command/CommandHandlersMapTemplate.php');

        $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/CommandHandlersMapCodeGeneratorWithInvalidHandlersData/FirstAggregate.php',
                __DIR__ . '/CommandHandlersMapCodeGeneratorWithInvalidHandlersData/SecondAggregate.php',
            ])
        );
    }
}
