<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

abstract class BaseAggregate
{
    #[AggregateEventApplier]
    public function whenEvent1(Event1 $event)
    {
    }
}
