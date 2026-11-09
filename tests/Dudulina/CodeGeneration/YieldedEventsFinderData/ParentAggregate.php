<?php

namespace tests\Dudulina\CodeGeneration\YieldedEventsFinderData;

use Dudulina\Attributes\AggregateCommandHandler;

class ParentAggregate
{
    #[AggregateCommandHandler]
    public function handleInParent(Command1 $command)
    {
        yield new Event1();
    }
}
