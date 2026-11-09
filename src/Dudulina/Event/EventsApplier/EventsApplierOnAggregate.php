<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventsApplier;


use Dudulina\Event\EventWithMetaData;

/**
 * Applies events on an aggregate. The method that applies an event is found, in this order:
 *  1. the method marked with #[AggregateEventApplier] whose first parameter is typed with the event class (any method name):
 *     taken from the pre-generated $appliersMap (see AggregateEventAppliersMapCodeGenerator) or, when there is no map,
 *     found once per aggregate class by reflection;
 *  2. unless $attributeOnly, the public method named apply<EventShortName> (the original naming convention).
 * An event without applier is ignored.
 *
 * Applying events is on the hot path (every command replays the aggregate's events), so everything that can be known before running
 * is computed before: with a generated map and $attributeOnly the work per event is two array lookups and the method call, with no
 * reflection, string building or is_callable().
 */
class EventsApplierOnAggregate
{
    /**
     * @var array<class-string, array<class-string, string|false>> aggregate class => event class => applier method name, or false = none;
     *      starts from the generated map and remembers the answers found at run time
     */
    private array $appliers = [];

    /** @var array<class-string, array<class-string, string>> */
    private array $markedAppliersFoundByReflection = [];

    /**
     * @param bool $attributeOnly when true only the methods marked with #[AggregateEventApplier] apply events
     *        and the apply<EventShortName> naming convention is ignored
     * @param array<class-string, array<class-string, string>>|null $appliersMap aggregate class => event class => method name, as generated
     *        into AggregateEventAppliersMap; null = find the marked methods by reflection at run time
     */
    public function __construct(
        private bool $attributeOnly = false,
        private ?array $appliersMap = null
    )
    {
    }

    /**
     * @param $aggregate
     * @param EventWithMetaData[] $priorEvents
     */
    public function applyEventsOnAggregate($aggregate, $priorEvents)
    {
        $aggregateClass = \get_class($aggregate);
        $appliers = $this->appliers[$aggregateClass] ??= $this->appliersMap[$aggregateClass] ?? [];

        foreach ($priorEvents as $eventWithMetaData) {
            $event = $eventWithMetaData->getEvent();
            $eventClass = \get_class($event);

            $methodName = $appliers[$eventClass] ?? null;

            if (null === $methodName) {
                $methodName = $this->appliers[$aggregateClass][$eventClass] = $appliers[$eventClass] = $this->findApplier($aggregate, $aggregateClass, $event, $eventClass);
            }

            if (false !== $methodName) {
                $aggregate->$methodName($event, $eventWithMetaData->getMetaData());
            }
        }
    }

    public static function getMethodName($event)
    {
        $parts = explode('\\', \get_class($event));
        return 'apply' . end($parts);
    }

    /** the slow path, taken once for each aggregate class and event class: @return string|false the applier's method name, false = none */
    private function findApplier($aggregate, string $aggregateClass, $event, string $eventClass)
    {
        if (null === $this->appliersMap) {
            $marked = ($this->markedAppliersFoundByReflection[$aggregateClass] ??= (new MarkedAppliersFinder())->findIn($aggregateClass))[$eventClass] ?? null;

            if (null !== $marked) {
                return $marked;
            }
        }

        if ($this->attributeOnly) {
            return false;
        }

        $conventionMethodName = self::getMethodName($event);

        return \is_callable([$aggregate, $conventionMethodName]) ? $conventionMethodName : false;
    }
}
