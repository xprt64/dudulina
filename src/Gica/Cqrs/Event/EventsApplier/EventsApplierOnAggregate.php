<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Event\EventsApplier;


use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;

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

    public static function getMethodName(Event $event)
    {
        $parts = explode('\\', get_class($event));

        return 'apply' . end($parts);
    }
}