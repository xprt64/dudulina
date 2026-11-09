<?php

namespace tests\Dudulina\CodeGeneration;

use Dudulina\Attributes\AggregateCommandHandler;
use Dudulina\CodeGeneration\Aggregate\YieldedEventsFinder;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\ChildAggregate;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\DirectAggregate;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\Event1;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\Event2;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\Event3;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\Event4;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\Event5;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\Event6;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\TraitAggregate;
use tests\Dudulina\CodeGeneration\YieldedEventsFinderData\UnresolvableAggregate;

class YieldedEventsFinderTest extends \PHPUnit\Framework\TestCase
{
    /** @return \ReflectionMethod[] */
    private function commandHandlersOf(string $class): array
    {
        return array_values(array_filter(
            (new \ReflectionClass($class))->getMethods(),
            fn(\ReflectionMethod $method) => [] !== $method->getAttributes(AggregateCommandHandler::class)
        ));
    }

    private function find(string $class): array
    {
        return (new YieldedEventsFinder())->findYieldedBy(new \ReflectionClass($class), $this->commandHandlersOf($class));
    }

    public function testResolvesEveryWayOfYieldingAnEvent()
    {
        $yielded = $this->find(DirectAggregate::class);

        $this->assertEqualsCanonicalizing(
            [Event1::class, Event2::class, Event3::class, Event4::class, Event5::class, Event6::class],
            array_keys($yielded),
            'new X; (new X)->withSomething(); yield from a helper; $variable = new X; a typed factory; a typed static factory; ' .
            'but not the yields of a closure nor of a method that is not a command handler'
        );
    }

    public function testTellsWhereEachEventIsYielded()
    {
        $yielded = $this->find(DirectAggregate::class);

        $this->assertCount(1, $yielded[Event1::class]);
        $this->assertStringContainsString('DirectAggregate::handleDirect (DirectAggregate.php:', $yielded[Event1::class][0]);
        $this->assertStringContainsString('DirectAggregate::helper (DirectAggregate.php:', $yielded[Event3::class][0], 'an event yielded by a helper is attributed to the helper');
    }

    public function testFollowsInheritedHandlersAndYieldFromParent()
    {
        $this->assertEqualsCanonicalizing([Event1::class, Event3::class], array_keys($this->find(ChildAggregate::class)));
    }

    public function testFindsTheYieldsOfHandlersDeclaredInATrait()
    {
        $this->assertSame([Event4::class], array_keys($this->find(TraitAggregate::class)));
    }

    public function testAYieldThatCannotBeResolvedFailsAndAllTheProblemsAreReported()
    {
        try {
            $this->find(UnresolvableAggregate::class);
            $this->fail('a LogicException was expected');
        } catch (\LogicException $exception) {
            $message = $exception->getMessage();
        }

        $this->assertStringContainsString('UnresolvableAggregate', $message);
        $this->assertStringContainsString('(UnresolvableAggregate.php:', $message);
        $this->assertSame(6, substr_count($message, 'UnresolvableAggregate::handleUnresolvable'), 'a problem for each of the six unresolvable yields: ' . $message);
        $this->assertStringContainsString('`yield from` something other than a method of the aggregate', $message);
    }

    public function testNoCommandHandlersNoEvents()
    {
        $this->assertSame([], (new YieldedEventsFinder())->findYieldedBy(new \ReflectionClass(DirectAggregate::class), []));
    }
}
