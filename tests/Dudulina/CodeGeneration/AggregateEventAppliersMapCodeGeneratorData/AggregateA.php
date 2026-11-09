<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class AggregateA
{
    #[AggregateEventApplier]
    public function whenEvent2(Event2 $event, \Dudulina\Event\MetaData $metaData)
    {
    }

    #[AggregateEventApplier]
    public function anotherName(Event1 $event)
    {
    }

    /** not marked: the generated map only lists the marked methods */
    public function applyEvent3(Event3 $event)
    {
    }
}
