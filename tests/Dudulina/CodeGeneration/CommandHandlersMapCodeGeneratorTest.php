<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorTest;

use Dudulina\CodeGeneration\Command\CommandHandlersMapCodeGenerator;

class CommandHandlersMapCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{

    const EXPECTED_MAP = [
        \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command1::class => [
            [
                \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\FirstAggregate::class,
                'handleCommand1',
            ],
        ],
        \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command2::class => [
            [
                \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\FirstAggregate::class,
                'handleCommand2',
            ],
        ],
        \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command3::class => [
            [
                \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\SecondAggregate::class,
                'handleCommand3',
            ],
        ],
        \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command4::class => [
            [
                \tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\SecondAggregate::class,
                'handleCommand4',
            ],
        ],
    ];

    public function test()
    {
        $sut = new CommandHandlersMapCodeGenerator();

        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Command/CommandHandlersMapTemplate.php');

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/CommandHandlersMapCodeGeneratorData/FirstAggregate.php',
                __DIR__ . '/CommandHandlersMapCodeGeneratorData/SecondAggregate.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);

        $mapper = new \Dudulina\CodeGeneration\Command\CommandHandlersMapTemplate();

        $map = $mapper->getCommandHandlersDefinitions();

        $this->assertCount(4, $map);

        $this->assertEquals(self::EXPECTED_MAP, $map);
    }

    private function evaluateGeneratedClass(string $content)
    {
        $content = str_replace('<?php', '', $content);
        eval($content);
    }
}
