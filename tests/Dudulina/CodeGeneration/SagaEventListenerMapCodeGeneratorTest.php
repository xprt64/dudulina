<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\Event\SagaEventProcessorsMapCodeGenerator;
use tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Event1;
use tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Event2;
use tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Saga1;
use tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Saga2;
use tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData\SagaEventProcessorsMap;
use tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData\SagaEventProcessorsMapTemplate;


class SagaEventListenerMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const EXPECTED_MAP = [
        Event2::class => [
            [
                Saga2::class,
                'processEvent2',
            ],
        ],
        Event1::class => [
            [
                Saga1::class,
                'processEvent1',
            ],
        ],
    ];

    public function test()
    {
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Event/SagaEventProcessorsMapTemplate.php');

        $sut = new SagaEventProcessorsMapCodeGenerator();

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/SagaEventListenerMapCodeGeneratorData/Saga1.php',
                __DIR__ . '/SagaEventListenerMapCodeGeneratorData/Saga2.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);

        $mapper = new \Dudulina\CodeGeneration\Event\SagaEventProcessorsMapTemplate();

        $map = $mapper->getMap();

        $this->assertCount(2, $map);

        $this->assertEquals(self::EXPECTED_MAP[Event1::class], $map[Event1::class]);
        $this->assertEquals(self::EXPECTED_MAP[Event2::class], $map[Event2::class]);

    }

    private function evaluateGeneratedClass($content)
    {
        $content = str_replace('<?php', '', $content);
        eval($content);
    }
}
