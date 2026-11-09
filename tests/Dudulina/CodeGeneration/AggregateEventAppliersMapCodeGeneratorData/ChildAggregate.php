<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class ChildAggregate extends BaseAggregate
{
    #[AggregateEventApplier]
    public function whenEvent3(Event3 $event)
    {
    }
}
