<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class InvalidPrivateAggregate
{
    #[AggregateEventApplier]
    private function whenEvent1(Event1 $event)
    {
    }
}
