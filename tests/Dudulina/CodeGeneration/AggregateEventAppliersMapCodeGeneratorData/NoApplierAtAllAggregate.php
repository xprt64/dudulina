<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateCommandHandler;

class NoApplierAtAllAggregate
{
    #[AggregateCommandHandler]
    public function handleIt(Command1 $command)
    {
        yield new Event2();
    }
}
