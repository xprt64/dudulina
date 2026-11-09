<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateCommandHandler;

class UnresolvableYieldAggregate
{
    #[AggregateCommandHandler]
    public function handleIt(Command1 $command)
    {
        yield $this->somethingUntyped();
    }

    private function somethingUntyped()
    {
        return new Event1();
    }
}
