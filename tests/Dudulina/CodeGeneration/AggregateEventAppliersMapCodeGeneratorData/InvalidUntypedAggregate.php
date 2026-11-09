<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class InvalidUntypedAggregate
{
    #[AggregateEventApplier]
    public function whenSomething($event)
    {
    }
}
