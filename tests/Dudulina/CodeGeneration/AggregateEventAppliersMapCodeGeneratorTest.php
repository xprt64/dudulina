<?php

namespace tests\Dudulina\CodeGeneration;

use Dudulina\CodeGeneration\Aggregate\AggregateEventAppliersMapCodeGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\AggregateA;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\AggregateB;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\BaseAggregate;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\ChildAggregate;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\Event1;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\Event2;
use tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\Event3;

class AggregateEventAppliersMapCodeGeneratorTest extends \PHPUnit\Framework\TestCase
{
    private const DATA = __DIR__ . '/AggregateEventAppliersMapCodeGeneratorData/';

    private static int $generatedClasses = 0;

    private function generateMap(array $files): array
    {
        $template = file_get_contents(__DIR__ . '/../../../src/Dudulina/CodeGeneration/Aggregate/AggregateEventAppliersMapTemplate.php');
        $generated = (new AggregateEventAppliersMapCodeGenerator())->generateClass(
            $template,
            new \ArrayIterator(array_map(fn(string $file) => self::DATA . $file . '.php', $files))
        );

        // evaluate the generated class under a name that is unique to this test run
        $className = 'GeneratedAggregateEventAppliersMap' . (++self::$generatedClasses);
        eval(str_replace(
            ['<?php', 'namespace Dudulina\CodeGeneration\Aggregate;', 'class AggregateEventAppliersMapTemplate'],
            ['', 'namespace tests\Dudulina\CodeGeneration\Generated;', 'class ' . $className],
            $generated
        ));

        return ('tests\Dudulina\CodeGeneration\Generated\\' . $className)::getMap();
    }

    public function testGeneratesTheMapOfTheMarkedMethodsWhateverTheirNames()
    {
        $map = $this->generateMap(['AggregateB', 'AggregateA', 'NotAnAggregate', 'Event1']);

        $this->assertSame([
            AggregateA::class => [
                Event1::class => 'anotherName',
                Event2::class => 'whenEvent2',
            ],
            AggregateB::class => [
                Event1::class => 'either',
                Event2::class => 'either',
            ],
        ], $map, 'sorted; unmarked apply* methods and classes without marked methods are left out');
    }

    public function testTheOutputDoesNotDependOnTheOrderOfTheFiles()
    {
        $this->assertSame(
            $this->generateMap(['AggregateA', 'AggregateB', 'ChildAggregate']),
            $this->generateMap(['ChildAggregate', 'AggregateB', 'AggregateA'])
        );
    }

    public function testAnInheritedApplierIsMappedUnderTheConcreteClassOnly()
    {
        $map = $this->generateMap(['BaseAggregate', 'ChildAggregate']);

        $this->assertSame([
            ChildAggregate::class => [
                Event1::class => 'whenEvent1',
                Event3::class => 'whenEvent3',
            ],
        ], $map, 'the abstract base class is not an aggregate, its child is');
        $this->assertArrayNotHasKey(BaseAggregate::class, $map);
    }

    public function testAnAggregateThatAppliesEveryEventItYieldsIsAccepted()
    {
        $map = $this->generateMap(['CompleteAggregate']);

        $this->assertSame(
            [Event1::class => 'applyEvent1', Event2::class => 'whenEvent2'],
            $map[\tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData\CompleteAggregate::class],
            'an empty applier (whenEvent2) is a valid applier'
        );
    }

    public function testAnEventYieldedByAnAggregateMustHaveAnApplier()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MissingApplierAggregate yields ' . Event3::class . ' (MissingApplierAggregate::handleIt (MissingApplierAggregate.php:');

        $this->generateMap(['MissingApplierAggregate']);
    }

    public function testAnAggregateWithoutAnyApplierFailsToo()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('NoApplierAtAllAggregate yields');

        $this->generateMap(['NoApplierAtAllAggregate']);
    }

    public function testAllTheProblemsOfAllTheAggregatesAreReportedTogether()
    {
        try {
            $this->generateMap(['MissingApplierAggregate', 'NoApplierAtAllAggregate', 'UnresolvableYieldAggregate']);
            $this->fail('a LogicException was expected');
        } catch (\LogicException $exception) {
            $message = $exception->getMessage();
        }

        $this->assertStringContainsString('MissingApplierAggregate yields', $message);
        $this->assertStringContainsString('NoApplierAtAllAggregate yields', $message);
        $this->assertStringContainsString('Cannot tell which events', $message);
        $this->assertStringContainsString('UnresolvableYieldAggregate', $message);
    }

    #[DataProvider('wrongMarkings')]
    public function testAWrongMarkingFailsTheGeneration(string $file, string $expectedMessage)
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->generateMap([$file]);
    }

    public static function wrongMarkings(): array
    {
        return [
            'not public' => ['InvalidPrivateAggregate', 'is not public'],
            'two appliers for one event' => ['InvalidDuplicateAggregate', 'both apply the event'],
            'first parameter is not typed' => ['InvalidUntypedAggregate', 'not typed with an event class'],
            'no parameter' => ['InvalidNoParameterAggregate', 'not typed with an event class'],
        ];
    }
}
