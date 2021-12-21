<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration;

use Dudulina\CodeGeneration\Event\ReadModelsMapCodeGenerator;
use Dudulina\CodeGeneration\Event\ReadModelsMapTemplate;


class ReadModelsMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const EXPECTED_MAP = [
        \tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\FirstReadModel::class => [
            [\tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event1::class, 'onEvent1'],
        ],

        \tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\subdir\SecondReadModel::class => [
            [\tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event2::class, 'onEvent2'],
        ],
    ];

    public function test_generate()
    {
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Event/ReadModelsMapTemplate.php');

        $sut = new ReadModelsMapCodeGenerator();

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/ReadModelsMapCodeGeneratorData/Events/Event1.php',
                __DIR__ . '/ReadModelsMapCodeGeneratorData/Events/Event2.php',
                __DIR__ . '/ReadModelsMapCodeGeneratorData/FirstReadModel.php',
                __DIR__ . '/ReadModelsMapCodeGeneratorData/subdir/SecondReadModel.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);

        $mapper = new ReadModelsMapTemplate();

        $map = $mapper->getMap();

        $this->assertCount(2, $map);

        $this->assertEquals(self::EXPECTED_MAP, $map);
    }

    private function evaluateGeneratedClass(string $content)
    {
        $content = str_replace('<?php', '', $content);
        eval($content);
    }
}
