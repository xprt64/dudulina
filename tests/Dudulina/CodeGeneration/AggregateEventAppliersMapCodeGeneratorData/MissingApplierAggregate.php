<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateCommandHandler;
use Dudulina\Attributes\AggregateEventApplier;

class MissingApplierAggregate
{
    #[AggregateCommandHandler]
    public function handleIt(Command1 $command)
    {
        yield new Event1();
        yield new Event3();
    }

    #[AggregateEventApplier]
    public function whenEvent1(Event1 $event)
    {
    }
}
