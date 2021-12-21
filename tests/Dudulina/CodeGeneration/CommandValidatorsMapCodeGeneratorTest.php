<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\Command\CommandValidatorsMapCodeGenerator;
use tests\Dudulina\CodeGeneration\CommandValidatorsMapCodeGeneratorData\Command1;
use tests\Dudulina\CodeGeneration\CommandValidatorsMapCodeGeneratorData\Command2;
use tests\Dudulina\CodeGeneration\CommandValidatorsMapCodeGeneratorData\ValidatorForCommand1;
use tests\Dudulina\CodeGeneration\CommandValidatorsMapCodeGeneratorData\ValidatorForCommand2;


class CommandValidatorsMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const EXPECTED_MAP = [
        Command2::class => [
            [
                ValidatorForCommand2::class,
                'validateCommand2',
            ],
        ],
        Command1::class => [
            [
                ValidatorForCommand1::class,
                'validateCommand1',
            ],
        ],
    ];

    public function test()
    {
        $sut = new CommandValidatorsMapCodeGenerator();
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Command/CommandValidatorSubscriberTemplate.php');

        $generated = $sut->generateClass(
            $template,
            new \ArrayIterator([
                __DIR__ . '/CommandValidatorsMapCodeGeneratorData/ValidatorForCommand1.php',
                __DIR__ . '/CommandValidatorsMapCodeGeneratorData/ValidatorForCommand2.php',
            ])
        );

        $this->evaluateGeneratedClass($generated);
        $mapper = new \Dudulina\CodeGeneration\Command\CommandValidatorSubscriberTemplate();
        $map = $mapper->getMap();
        $this->assertCount(2, $map);
        $this->assertEquals(self::EXPECTED_MAP[Command1::class], $map[Command1::class]);
        $this->assertEquals(self::EXPECTED_MAP[Command2::class], $map[Command2::class]);
    }

    private function evaluateGeneratedClass(string $content)
    {
        $content = str_replace('<?php', '', $content);
        eval($content);
    }
}
