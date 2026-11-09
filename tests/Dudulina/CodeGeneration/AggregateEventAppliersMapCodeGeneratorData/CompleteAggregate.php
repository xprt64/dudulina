<?php

namespace tests\Dudulina\CodeGeneration\AggregateEventAppliersMapCodeGeneratorData;

use Dudulina\Attributes\AggregateCommandHandler;
use Dudulina\Attributes\AggregateEventApplier;

class CompleteAggregate
{
    #[AggregateCommandHandler]
    public function handleIt(Command1 $command)
    {
        yield new Event1();
        yield new Event2();
    }

    #[AggregateEventApplier]
    public function applyEvent1(Event1 $event)
    {
        $this->state = 1;
    }

    #[AggregateEventApplier]
    public function whenEvent2(Event2 $event)
    {
        // no state to update: the event only informs the read models
    }

    private int $state = 0;
}
