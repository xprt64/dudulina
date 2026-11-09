<?php
/*
 * Copyright (c) 2025 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Event\EventsApplier;

use Dudulina\Attributes\AggregateEventApplier;

/**
 * Finds, by reflection, the methods of an aggregate class that are marked with #[AggregateEventApplier], and validates them.
 *
 * This is the single place that knows the rules; it runs when the maps are generated (AggregateEventAppliersMapCodeGenerator),
 * so that a wrong marking fails the build, and as a fallback in EventsApplierOnAggregate when it has no generated map.
 */
class MarkedAppliersFinder
{
    /**
     * @return array<class-string, string> event class => name of the aggregate's method that applies it
     * @throws \LogicException when a marked method is not public, its first parameter is not typed with an event class,
     *         or two marked methods apply the same event
     */
    public function findIn(string $aggregateClass): array
    {
        $appliers = [];

        foreach ((new \ReflectionClass($aggregateClass))->getMethods() as $method) {
            if (!$method->getAttributes(AggregateEventApplier::class)) {
                continue;
            }

            if (!$method->isPublic()) {
                throw new \LogicException(sprintf('%s::%s is marked with #[AggregateEventApplier] but it is not public', $aggregateClass, $method->getName()));
            }

            foreach ($this->eventClassesOf($method) as $eventClass) {
                if (isset($appliers[$eventClass])) {
                    throw new \LogicException(sprintf('%s::%s and %s::%s both apply the event %s', $aggregateClass, $appliers[$eventClass], $aggregateClass, $method->getName(), $eventClass));
                }
                $appliers[$eventClass] = $method->getName();
            }
        }

        return $appliers;
    }

    /** @return string[] the event classes the first parameter of the method is typed with */
    private function eventClassesOf(\ReflectionMethod $method): array
    {
        $type = ($method->getParameters()[0] ?? null)?->getType();
        $types = $type instanceof \ReflectionUnionType ? $type->getTypes() : [$type];

        $classes = [];
        foreach ($types as $singleType) {
            if ($singleType instanceof \ReflectionNamedType && !$singleType->isBuiltin() && !\in_array($singleType->getName(), ['self', 'static'], true)) {
                $classes[] = $singleType->getName();
            }
        }

        if (!$classes) {
            throw new \LogicException(sprintf('%s::%s is marked with #[AggregateEventApplier] but its first parameter is not typed with an event class', $method->getDeclaringClass()->getName(), $method->getName()));
        }

        return $classes;
    }
}
