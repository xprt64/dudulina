<?php

namespace tests\Dudulina\CodeGeneration\AttributeOnlyDetectorsTest;

use Dudulina\Attributes\AggregateCommandHandler;
use Dudulina\Attributes\EventListener;
use Dudulina\Attributes\EventProcessor;
use Dudulina\Attributes\QueryAsker;
use Dudulina\Attributes\QueryHandler;
use Dudulina\CodeGeneration\Command\AggregateCommandHandlerDetector;
use Dudulina\CodeGeneration\Command\ReadModelEventHandlerDetector;
use Dudulina\CodeGeneration\Command\WriteSideEventHandlerDetector;
use Dudulina\CodeGeneration\Query\QueryAskerDetector;
use Dudulina\CodeGeneration\Query\QueryHandlerDetector;
use Gica\CodeAnalysis\MethodListenerDiscovery\MessageClassDetector;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Every detector accepts, by default, a method marked with the attribute, one named by convention
 * or one marked with the docblock tag; with $attributeOnly = true only the attribute counts.
 */
class AttributeOnlyDetectorsTest extends \PHPUnit\Framework\TestCase
{
    public static function detectors(): array
    {
        return [
            'command handler' => [fn(bool $attributeOnly) => new AggregateCommandHandlerDetector($attributeOnly), HandlerFixture::class, 'handleThing', 'handleByDocblock'],
            'event listener' => [fn(bool $attributeOnly) => new ReadModelEventHandlerDetector($attributeOnly), ListenerFixture::class, 'onThing', 'byDocblock'],
            'event processor' => [fn(bool $attributeOnly) => new WriteSideEventHandlerDetector($attributeOnly), ProcessorFixture::class, 'processThing', 'byDocblock'],
            'query handler' => [fn(bool $attributeOnly) => new QueryHandlerDetector($attributeOnly), QueryHandlerFixture::class, 'whenAskedThing', 'byDocblock'],
            'query asker' => [fn(bool $attributeOnly) => new QueryAskerDetector($attributeOnly), QueryAskerFixture::class, 'whenAnsweredThing', 'byDocblock'],
        ];
    }

    #[DataProvider('detectors')]
    public function testDefaultModeAcceptsAttributeNameAndDocblock(\Closure $factory, string $fixture, string $nameMethod, string $docblockMethod)
    {
        $sut = $factory(false);

        $this->assertTrue($sut->isMethodAccepted(new \ReflectionMethod($fixture, 'anyNameWithAttribute')));
        $this->assertTrue($sut->isMethodAccepted(new \ReflectionMethod($fixture, $nameMethod)));
        $this->assertTrue($sut->isMethodAccepted(new \ReflectionMethod($fixture, $docblockMethod)));
        $this->assertFalse($sut->isMethodAccepted(new \ReflectionMethod($fixture, 'somethingElse')));
    }

    #[DataProvider('detectors')]
    public function testAttributeOnlyModeAcceptsOnlyTheAttribute(\Closure $factory, string $fixture, string $nameMethod, string $docblockMethod)
    {
        $sut = $factory(true);

        $this->assertTrue($sut->isMethodAccepted(new \ReflectionMethod($fixture, 'anyNameWithAttribute')));
        $this->assertFalse($sut->isMethodAccepted(new \ReflectionMethod($fixture, $nameMethod)), 'the method-name convention must be ignored');
        $this->assertFalse($sut->isMethodAccepted(new \ReflectionMethod($fixture, $docblockMethod)), 'the docblock tag must be ignored');
        $this->assertFalse($sut->isMethodAccepted(new \ReflectionMethod($fixture, 'somethingElse')));
    }

    public function testTheMessageClassCheckDoesNotDependOnTheMode()
    {
        foreach ([false, true] as $attributeOnly) {
            $this->assertFalse((new AggregateCommandHandlerDetector($attributeOnly))->isMessageClass(new \ReflectionClass(\stdClass::class)));
            $this->assertFalse((new ReadModelEventHandlerDetector($attributeOnly))->isMessageClass(new \ReflectionClass(\stdClass::class)));
            $this->assertTrue((new QueryHandlerDetector($attributeOnly))->isMessageClass(new \ReflectionClass(\stdClass::class)));
        }
    }
}

class HandlerFixture
{
    #[AggregateCommandHandler]
    public function anyNameWithAttribute($x) {}

    public function handleThing($x) {}

    /** @AggregateCommandHandler */
    public function handleByDocblock($x) {}

    public function somethingElse($x) {}
}

class ListenerFixture
{
    #[EventListener]
    public function anyNameWithAttribute($x) {}

    public function onThing($x) {}

    /** @EventListener */
    public function byDocblock($x) {}

    public function somethingElse($x) {}
}

class ProcessorFixture
{
    #[EventProcessor]
    public function anyNameWithAttribute($x) {}

    public function processThing($x) {}

    /** @EventProcessor */
    public function byDocblock($x) {}

    public function somethingElse($x) {}
}

class QueryHandlerFixture
{
    #[QueryHandler]
    public function anyNameWithAttribute($x) {}

    public function whenAskedThing($x) {}

    /** @QueryHandler */
    public function byDocblock($x) {}

    public function somethingElse($x) {}
}

class QueryAskerFixture
{
    #[QueryAsker]
    public function anyNameWithAttribute($x) {}

    public function whenAnsweredThing($x) {}

    /** @QueryAsker */
    public function byDocblock($x) {}

    public function somethingElse($x) {}
}
