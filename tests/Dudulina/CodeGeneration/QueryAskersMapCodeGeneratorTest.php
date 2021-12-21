<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration;

use tests\Dudulina\CodeGeneration\QueryAskersMapCodeGeneratorData\ReadModel1;
use tests\Dudulina\CodeGeneration\QueryAskersMapCodeGeneratorData\ReadModel2;
use tests\Dudulina\CodeGeneration\QueryAskersMapCodeGeneratorData\WhatIsTheAnswerToOneMinusOne;
use tests\Dudulina\CodeGeneration\QueryAskersMapCodeGeneratorData\WhatIsTheAnswerToOnePlusOne;


class QueryAskersMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const EXPECTED_MAP = [
        WhatIsTheAnswerToOneMinusOne::class => [
            [
                ReadModel2::class,
                'whenAnsweredWhatIsTheAnswerToOnePlusOne',
            ],
        ],
        WhatIsTheAnswerToOnePlusOne::class => [
            [
                ReadModel1::class,
                'whenAnsweredWhatIsTheAnswerToOnePlusOne',
            ],
        ],
    ];

    public function test()
    {
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Query/QueryAskersMapTemplate.php');

        $sut = new \Dudulina\CodeGeneration\Query\QueryAskersMapCodeGenerator();

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/QueryAskersMapCodeGeneratorData/ReadModel1.php',
                __DIR__ . '/QueryAskersMapCodeGeneratorData/ReadModel2.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);

        $mapper = new \Dudulina\CodeGeneration\Query\QueryAskersMapTemplate();

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
