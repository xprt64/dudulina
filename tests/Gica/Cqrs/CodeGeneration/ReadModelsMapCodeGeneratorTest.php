<?php


namespace tests\Gica\Cqrs\CodeGeneration;


use Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGenerator;
use Gica\FileSystem\FileSystemInterface;
use Gica\FileSystem\InMemoryFileSystem;
use tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\ReadModelsMap;
use tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\ReadModelsMapTemplate;


class ReadModelsMapCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{

    const EXPECTED_MAP = [
        \tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\FirstReadModel::class => [
            [\tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event1::class, 'onEvent1'],
        ],

        \tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\subdir\SecondReadModel::class => [
            [\tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event2::class, 'onEvent2'],
        ],
    ];

    public function test_generate()
    {
        $fileSystem = $this->stubFileSystem();

        $sut = new ReadModelsMapCodeGenerator(
            $this->mockLogger(),
            $fileSystem
        );

        $sut->generate(
            ReadModelsMapTemplate::class,
            __DIR__ . '/ReadModelsMapCodeGeneratorData',
            __DIR__ . '/ReadModelsMapCodeGeneratorData/ReadModelsMap.php',
            'ReadModelsMap'
        );

        $this->evaluateGeneratedClass($fileSystem);

        /** @var ReadModelsMapTemplate $mapper */
        /** @noinspection PhpUndefinedClassInspection */
        $mapper = new ReadModelsMap();

        $map = $mapper->getEventHandlersDefinitions();

        $this->assertCount(2, $map);

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
        if (class_exists(ReadModelsMap::class)) {
            return;
        }

        $content = $fileSystem->fileGetContents(__DIR__ . '/ReadModelsMapCodeGeneratorData/ReadModelsMap.php');
        $content = str_replace('<?php', '', $content);
        eval($content);
    }

    private function stubFileSystem(): InMemoryFileSystem
    {
        $fileSystem = new InMemoryFileSystem();

        $fileSystem->makeDirectory(__DIR__ . '/ReadModelsMapCodeGeneratorData', 0777, true);
        $fileSystem->filePutContents(__DIR__ . '/ReadModelsMapCodeGeneratorData/ReadModelsMapTemplate.php', file_get_contents(__DIR__ . '/ReadModelsMapCodeGeneratorData/ReadModelsMapTemplate.php'));
        return $fileSystem;
    }
}
