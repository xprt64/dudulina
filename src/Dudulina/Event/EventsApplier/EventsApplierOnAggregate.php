<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventsApplier;


use Dudulina\Attributes\AggregateEventApplier;
use Dudulina\Event\EventWithMetaData;

/**
 * Applies events on an aggregate. The method that applies an event is found, in this order:
 *  1. the public method marked with #[AggregateEventApplier] whose first parameter is typed with the event class (any method name);
 *  2. unless $attributeOnly, the public method named apply<EventShortName> (the original naming convention).
 * An event without applier is ignored.
 */
class EventsApplierOnAggregate
{
    /** @var array<class-string, array<class-string, string>> aggregate class => event class => applier method name */
    private static array $markedAppliersByAggregateClass = [];

    /**
     * @param bool $attributeOnly when true only the methods marked with #[AggregateEventApplier] apply events
     *        and the apply<EventShortName> naming convention is ignored
     */
    public function __construct(private bool $attributeOnly = false)
    {
    }

    /**
     * @param $aggregate
     * @param EventWithMetaData[] $priorEvents
     */
    public function applyEventsOnAggregate($aggregate, $priorEvents)
    {
        foreach ($priorEvents as $event) {
            $this->applyEvent($aggregate, $event);
        }
    }

    private function applyEvent($aggregate, EventWithMetaData $eventWithMetaData)
    {
        $event = $eventWithMetaData->getEvent();
        $methodName = $this->findApplierMethodName($aggregate, $event);

        if (null !== $methodName) {
            $aggregate->$methodName($event, $eventWithMetaData->getMetaData());
        }
    }

    private function findApplierMethodName($aggregate, $event): ?string
    {
        $markedMethodName = self::markedAppliersOf(\get_class($aggregate))[\get_class($event)] ?? null;

        if (null !== $markedMethodName) {
            return $markedMethodName;
        }

        if ($this->attributeOnly) {
            return null;
        }

        $conventionMethodName = self::getMethodName($event);

        return \is_callable([$aggregate, $conventionMethodName]) ? $conventionMethodName : null;
    }

    public static function getMethodName($event)
    {
        $parts = explode('\\', \get_class($event));
        return 'apply' . end($parts);
    }

    /** @return array<class-string, string> event class => applier method name */
    private static function markedAppliersOf(string $aggregateClass): array
    {
        return self::$markedAppliersByAggregateClass[$aggregateClass] ??= self::findMarkedAppliers($aggregateClass);
    }

    private static function findMarkedAppliers(string $aggregateClass): array
    {
        $appliers = [];

        foreach ((new \ReflectionClass($aggregateClass))->getMethods() as $method) {
            if (!$method->getAttributes(AggregateEventApplier::class)) {
                continue;
            }

            if (!$method->isPublic()) {
                throw new \LogicException(sprintf('%s::%s is marked with #[AggregateEventApplier] but it is not public', $aggregateClass, $method->getName()));
            }

            foreach (self::eventClassesOf($method) as $eventClass) {
                if (isset($appliers[$eventClass])) {
                    throw new \LogicException(sprintf('%s::%s and %s::%s both apply the event %s', $aggregateClass, $appliers[$eventClass], $aggregateClass, $method->getName(), $eventClass));
                }
                $appliers[$eventClass] = $method->getName();
            }
        }

        return $appliers;
    }

    /** @return string[] the event classes the first parameter of the method is typed with */
    private static function eventClassesOf(\ReflectionMethod $method): array
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
