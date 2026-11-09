<?php
/*
 * Copyright (c) 2025 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Aggregate;

use Dudulina\CodeGeneration\CodeGenerator;
use Dudulina\CodeGeneration\Command\AggregateCommandHandlerDetector;
use Dudulina\Event\EventsApplier\MarkedAppliersFinder;
use Gica\CodeAnalysis\PhpClassInFileInspector;

/**
 * Generates the map used by EventsApplierOnAggregate: for every class that has methods marked with #[AggregateEventApplier],
 * the method that applies each event: [Aggregate::class => [SomethingHappened::class => 'whenSomethingHappened']].
 *
 * The lookup that would otherwise be done by reflection while applying events is done here, once, and everything that can be wrong
 * fails the generation with a LogicException instead of being found out while the application runs:
 *  - a marked method that is not public, whose first parameter is not typed with an event class, or two methods applying one event;
 *  - an event that an aggregate yields from its command handlers (see YieldedEventsFinder) and that it has no applier for: such an
 *    event would be silently ignored when applied. An event that changes no state needs an empty applier, which says so explicitly.
 * The output is sorted, so it does not change between runs.
 */
class AggregateEventAppliersMapCodeGenerator implements CodeGenerator
{
    const MARKER = '[/*do not modify this line!*/]';

    public function __construct(
        private ?MarkedAppliersFinder $finder = null,
        private ?PhpClassInFileInspector $inspector = null,
        private ?AggregateCommandHandlerDetector $commandHandlerDetector = null,
        private ?YieldedEventsFinder $yieldedEventsFinder = null
    )
    {
        $this->finder ??= new MarkedAppliersFinder();
        $this->inspector ??= new PhpClassInFileInspector();
        $this->commandHandlerDetector ??= new AggregateCommandHandlerDetector();
        $this->yieldedEventsFinder ??= new YieldedEventsFinder();
    }

    public function generateClass(string $template, \Iterator $filesToSearchForHandlers): string
    {
        return str_replace(self::MARKER, $this->toCode($this->findAppliers($filesToSearchForHandlers)), $template);
    }

    /** @return array<class-string, array<class-string, string>> */
    private function findAppliers(\Iterator $files): array
    {
        $map = [];
        $problems = [];

        foreach ($files as $file) {
            if (!preg_match('#\.php$#i', (string)$file)) {
                continue;
            }

            $class = $this->inspector->getFullyQualifiedClassName((string)$file);

            if (null === $class) {
                continue;
            }

            $class = ltrim($class, '\\');
            $reflection = new \ReflectionClass($class);

            // only the classes that can be an aggregate: traits and abstract classes are covered by the classes that use / extend them
            if ($reflection->isTrait() || $reflection->isInterface() || $reflection->isAbstract() || $reflection->isEnum()) {
                continue;
            }

            $appliers = $this->finder->findIn($class);

            array_push($problems, ...$this->findYieldedEventsWithoutApplier($reflection, $appliers));

            if ($appliers) {
                ksort($appliers);
                $map[$class] = $appliers;
            }
        }

        if ($problems) {
            throw new \LogicException(
                "An event that an aggregate yields must be applied by it, otherwise it is ignored when the aggregate is rebuilt.\n" .
                "Add a method marked with #[AggregateEventApplier] for it (an empty one if the event changes no state):\n  " . implode("\n  ", $problems)
            );
        }

        ksort($map);

        return $map;
    }

    /** @return string[] */
    private function findYieldedEventsWithoutApplier(\ReflectionClass $aggregate, array $appliers): array
    {
        $commandHandlers = $this->findCommandHandlers($aggregate);

        if (!$commandHandlers) {
            return [];
        }

        try {
            $yielded = $this->yieldedEventsFinder->findYieldedBy($aggregate, $commandHandlers);
        } catch (\LogicException $exception) {
            return [$exception->getMessage()];
        }

        $problems = [];
        foreach ($yielded as $eventClass => $places) {
            if (!isset($appliers[$eventClass])) {
                $problems[] = sprintf('%s yields %s (%s) but has no applier for it', $aggregate->getName(), $eventClass, implode(', ', array_unique($places)));
            }
        }

        return $problems;
    }

    /** @return \ReflectionMethod[] the methods that handle a command: accepted by the detector and taking a command */
    private function findCommandHandlers(\ReflectionClass $aggregate): array
    {
        $handlers = [];

        foreach ($aggregate->getMethods() as $method) {
            if (!$this->commandHandlerDetector->isMethodAccepted($method)) {
                continue;
            }

            $type = ($method->getParameters()[0] ?? null)?->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin() && class_exists($type->getName())
                && $this->commandHandlerDetector->isMessageClass(new \ReflectionClass($type->getName()))) {
                $handlers[] = $method;
            }
        }

        return $handlers;
    }

    private function toCode(array $map): string
    {
        $classes = [];

        foreach ($map as $aggregateClass => $appliers) {
            $entries = [];
            foreach ($appliers as $eventClass => $methodName) {
                $entries[] = str_repeat(' ', 16) . '\\' . $eventClass . "::class => '" . $methodName . "',";
            }
            $classes[] = str_repeat(' ', 12) . '\\' . $aggregateClass . "::class => [\n" . implode("\n", $entries) . "\n" . str_repeat(' ', 12) . '],';
        }

        return "[\n" . implode("\n\n", $classes) . "\n" . str_repeat(' ', 8) . ']';
    }
}
