<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\Event\EventListenersMapCodeGenerator;
use Psr\Container\ContainerInterface;
use tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\Event1;
use tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\Event2;
use tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModel1;
use tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModel2;
use tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModelMap;


class ReadModelEventListenersMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const EXPECTED_MAP = [
        Event2::class => [
            [
                ReadModel2::class,
                'onEvent2',
            ],
        ],
        Event1::class => [
            [
                ReadModel1::class,
                'Event1',
            ],
        ],
    ];

    public function test()
    {
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Event/EventListenersMapTemplate.php');

        $sut = new EventListenersMapCodeGenerator();

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/ReadModelEventListenersMapCodeGeneratorData/ReadModel1.php',
                __DIR__ . '/ReadModelEventListenersMapCodeGeneratorData/ReadModel2.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $mapper = new \Dudulina\CodeGeneration\Event\EventListenersMapTemplate($container);

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
