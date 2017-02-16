<?php


namespace tests\Gica\Cqrs\CodeGeneration;


use Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGenerator;
use Gica\FileSystem\FileSystemInterface;
use Gica\FileSystem\InMemoryFileSystem;
use tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\Event1;
use tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\Event2;
use tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModel1;
use tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModel2;
use tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModelMap;
use tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData\ReadModelMapTemplate;


class ReadModelEventListenersMapCodeGeneratorTest extends \PHPUnit_Framework_TestCase
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
                'onEvent1',
            ],
        ],
    ];

    public function test()
    {
        $fileSystem = $this->stubFileSystem();

        $sut = new ReadModelEventListenersMapCodeGenerator(
            $this->mockLogger(),
            $fileSystem
        );

        $sut->generate(
            ReadModelMapTemplate::class,
            __DIR__ . '/ReadModelEventListenersMapCodeGeneratorData',
            __DIR__ . '/ReadModelEventListenersMapCodeGeneratorData/ReadModelMap.php',
            'ReadModelMap'
        );

        $this->evaluateGeneratedClass($fileSystem);

        /** @noinspection PhpUndefinedClassInspection */
        $mapper = new ReadModelMap();

        $map = $mapper->getMap();

        $this->assertCount(2, $map);

        $this->assertEquals(self::EXPECTED_MAP[Event1::class], $map[Event1::class]);
        $this->assertEquals(self::EXPECTED_MAP[Event2::class], $map[Event2::class]);

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
        if (class_exists(ReadModelMap::class)) {
            return;
        }

        $content = $fileSystem->fileGetContents(__DIR__ . '/ReadModelEventListenersMapCodeGeneratorData/ReadModelMap.php');
        $content = str_replace('<?php', '', $content);
        eval($content);
    }

    private function stubFileSystem(): InMemoryFileSystem
    {
        $fileSystem = new InMemoryFileSystem();

        $fileSystem->makeDirectory(__DIR__ . '/ReadModelEventListenersMapCodeGeneratorData', 0777, true);
        $fileSystem->filePutContents(
            __DIR__ . '/ReadModelEventListenersMapCodeGeneratorData/ReadModelMapTemplate.php',
            file_get_contents(__DIR__ . '/ReadModelEventListenersMapCodeGeneratorData/ReadModelMapTemplate.php'));
        return $fileSystem;
    }
}
