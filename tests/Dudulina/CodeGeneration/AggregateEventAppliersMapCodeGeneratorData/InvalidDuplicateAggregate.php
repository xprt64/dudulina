<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class InvalidDuplicateAggregate
{
    #[AggregateEventApplier]
    public function first(Event1 $event)
    {
    }

    #[AggregateEventApplier]
    public function second(Event1 $event)
    {
    }
}
