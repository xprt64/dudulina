<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration;

use tests\Dudulina\CodeGeneration\QueryHandlersMapCodeGeneratorData\ReadModel1;
use tests\Dudulina\CodeGeneration\QueryHandlersMapCodeGeneratorData\ReadModel2;
use tests\Dudulina\CodeGeneration\QueryHandlersMapCodeGeneratorData\WhatIsTheAnswerToOneMinusOne;
use tests\Dudulina\CodeGeneration\QueryHandlersMapCodeGeneratorData\WhatIsTheAnswerToOnePlusOne;


class QueryHandlersMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const EXPECTED_MAP = [
        WhatIsTheAnswerToOneMinusOne::class => [
            [
                ReadModel2::class,
                'whenAskedWhatIsTheAnswerToOnePlusOne',
            ],
        ],
        WhatIsTheAnswerToOnePlusOne::class  => [
            [
                ReadModel1::class,
                'whenAskedWhatIsTheAnswerToOnePlusOne',
            ],
        ],
    ];

    public function test()
    {
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Query/QueryHandlersMapTemplate.php');

        $sut = new \Dudulina\CodeGeneration\Query\QueryHandlersMapCodeGenerator();

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/QueryHandlersMapCodeGeneratorData/ReadModel1.php',
                __DIR__ . '/QueryHandlersMapCodeGeneratorData/ReadModel2.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);

        $mapper = new \Dudulina\CodeGeneration\Query\QueryHandlersMapTemplate();

        $map = $mapper->getMap();

        $this->assertCount(2, $map);

        $this->assertEquals(self::EXPECTED_MAP[WhatIsTheAnswerToOnePlusOne::class], $map[WhatIsTheAnswerToOnePlusOne::class]);
        $this->assertEquals(self::EXPECTED_MAP[WhatIsTheAnswerToOneMinusOne::class], $map[WhatIsTheAnswerToOneMinusOne::class]);
    }

    private function evaluateGeneratedClass($content)
    {
        $content = str_replace('<?php', '', $content);
        eval($content);
    }
}
