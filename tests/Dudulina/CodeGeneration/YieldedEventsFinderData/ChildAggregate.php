<?php

namespace tests\Dudulina\CodeGeneration\YieldedEventsFinderData;

use Dudulina\Attributes\AggregateCommandHandler;

class ChildAggregate extends ParentAggregate
{
    #[AggregateCommandHandler]
    public function handleInChild(Command1 $command)
    {
        yield new Event3();
        yield from parent::handleInParent($command);
    }
}
