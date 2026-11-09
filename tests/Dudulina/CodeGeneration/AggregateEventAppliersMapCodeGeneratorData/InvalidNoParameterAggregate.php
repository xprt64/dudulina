<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class InvalidNoParameterAggregate
{
    #[AggregateEventApplier]
    public function whenSomething()
    {
    }
}
