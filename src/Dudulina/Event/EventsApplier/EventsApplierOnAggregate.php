<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Event\EventsApplier;


use Dudulina\Event\EventWithMetaData;

class EventsApplierOnAggregate
{
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
        $methodName = self::getMethodName($eventWithMetaData->getEvent());

        if (is_callable([$aggregate, $methodName])) {
            call_user_func([$aggregate, $methodName], $eventWithMetaData->getEvent(), $eventWithMetaData->getMetaData());
        }
    }

    public static function getMethodName($event)
    {
        $parts = explode('\\', get_class($event));

        return 'apply' . end($parts);
    }
}