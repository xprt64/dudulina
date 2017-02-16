<?php


namespace tests\Gica\Cqrs\CodeGeneration;


use Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGenerator;
use Gica\FileSystem\FileSystemInterface;
use Gica\FileSystem\InMemoryFileSystem;
use tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\CommandHandlersMap;
use tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\CommandHandlersMapTemplate;


class CommandHandlersMapCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{

    const EXPECTED_MAP = [
        \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command1::class => [
            [
                \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\FirstAggregate::class,
                'handleCommand1',
            ],
        ],
        \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command2::class => [
            [
                \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\FirstAggregate::class,
                'handleCommand2',
            ],
        ],
        \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command3::class => [
            [
                \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\SecondAggregate::class,
                'handleCommand3',
            ],
        ],
        \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\Command4::class => [
            [
                \tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData\SecondAggregate::class,
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
            __DIR__ . '/CommandHandlersMapCodeGeneratorData',
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
