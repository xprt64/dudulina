<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateEventApplier;

class AggregateB
{
    #[AggregateEventApplier]
    public function either(Event1|Event2 $event)
    {
    }
}
