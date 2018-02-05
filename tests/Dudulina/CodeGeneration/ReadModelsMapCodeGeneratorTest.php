<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\ReadModelsMapCodeGenerator;
use Gica\FileSystem\FileSystemInterface;
use Gica\FileSystem\InMemoryFileSystem;
use tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\ReadModelsMap;
use tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\ReadModelsMapTemplate;


class ReadModelsMapCodeGeneratorTest extends \PHPUnit_Framework_TestCase
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
        $fileSystem = $this->stubFileSystem();

        $sut = new ReadModelsMapCodeGenerator(
            $this->mockLogger(),
            $fileSystem
        );

        $sut->generate(
            ReadModelsMapTemplate::class,
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/ReadModelsMapCodeGeneratorData')),
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
