<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\CommandHandlersMapCodeGenerator;
use Gica\FileSystem\FileSystemInterface;
use Gica\FileSystem\InMemoryFileSystem;
use tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\CommandHandlersMap;
use tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData\CommandHandlersMapTemplate;


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
        $fileSystem = $this->stubFileSystem();

        $sut = new CommandHandlersMapCodeGenerator(
            $this->mockLogger(),
            $fileSystem
        );

        $fileSystem->filePutContents(__DIR__ . '/CommandHandlersMapCodeGeneratorData/CommandHandlersMap.php', 'some content');

        $sut->generate(
            CommandHandlersMapTemplate::class,
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/CommandHandlersMapCodeGeneratorData')),
            __DIR__ . '/CommandHandlersMapCodeGeneratorData/CommandHandlersMap.php',
            'CommandHandlersMap'
        );

        $this->evaluateGeneratedClass($fileSystem);

        /** @var CommandHandlersMapTemplate $mapper */
        /** @noinspection PhpUndefinedClassInspection */
        $mapper = new CommandHandlersMap();

        $map = $mapper->getCommandHandlersDefinitions();

        $this->assertCount(4, $map);

        $this->assertEquals(self::EXPECTED_MAP, $map);

    }

    private function mockLogger()
    {
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();
        return $logger;
        /** @var \Psr\Log\LoggerInterface $logger */
    }

    private function evaluateGeneratedClass(FileSystemInterface $fileSystem)
    {
        /** @noinspection PhpUndefinedClassInspection */
        if (class_exists(CommandHandlersMap::class)) {
            return;
        }

        $content = $fileSystem->fileGetContents(__DIR__ . '/CommandHandlersMapCodeGeneratorData/CommandHandlersMap.php');
        $content = str_replace('<?php', '', $content);
        eval($content);
    }

    private function stubFileSystem(): InMemoryFileSystem
    {
        $fileSystem = new InMemoryFileSystem();

        $fileSystem->makeDirectory(__DIR__ . '/CommandHandlersMapCodeGeneratorData', 0777, true);
        $fileSystem->filePutContents(
            __DIR__ . '/CommandHandlersMapCodeGeneratorData/CommandHandlersMapTemplate.php',
            file_get_contents(__DIR__ . '/CommandHandlersMapCodeGeneratorData/CommandHandlersMapTemplate.php'));
        return $fileSystem;
    }
}
