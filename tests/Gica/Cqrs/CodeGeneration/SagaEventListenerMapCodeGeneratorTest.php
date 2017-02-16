<?php


namespace tests\Gica\Cqrs\CodeGeneration;


use Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGenerator;
use Gica\FileSystem\FileSystemInterface;
use Gica\FileSystem\InMemoryFileSystem;
use tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Event1;
use tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Event2;
use tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Saga1;
use tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData\Saga2;
use tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData\SagaEventProcessorsMap;
use tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData\SagaEventProcessorsMapTemplate;


class SagaEventListenerMapCodeGeneratorTest extends \PHPUnit_Framework_TestCase
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
        $fileSystem = $this->stubFileSystem();

        $sut = new SagaEventListenerMapCodeGenerator(
            $this->mockLogger(),
            $fileSystem);

        $sut->generate(
            SagaEventProcessorsMapTemplate::class,
            __DIR__ . '/SagaEventListenerMapCodeGeneratorData',
            __DIR__ . '/SagaEventListenerMapCodeGeneratorData/SagaEventProcessorsMap.php',
            'SagaEventProcessorsMap'
        );

        $this->evaluateGeneratedClass($fileSystem);

        $mapper = new SagaEventProcessorsMap();

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
        if (class_exists(SagaEventProcessorsMap::class)) {
            return;
        }

        $content = $fileSystem->fileGetContents(__DIR__ . '/SagaEventListenerMapCodeGeneratorData/SagaEventProcessorsMap.php');
        $content = str_replace('<?php', '', $content);
        eval($content);
    }

    private function stubFileSystem(): InMemoryFileSystem
    {
        $fileSystem = new InMemoryFileSystem();

        $fileSystem->makeDirectory(__DIR__ . '/SagaEventListenerMapCodeGeneratorData', 0777, true);
        $fileSystem->filePutContents(
            __DIR__ . '/SagaEventListenerMapCodeGeneratorData/SagaEventProcessorsMapTemplate.php',
            file_get_contents(__DIR__ . '/SagaEventListenerMapCodeGeneratorData/SagaEventProcessorsMapTemplate.php'));
        return $fileSystem;
    }

}
