<?php

namespace tests\Dudulina\CodeGeneration\YieldedEventsFinderData;

use Dudulina\Attributes\AggregateCommandHandler;

trait HandlerTrait
{
    #[AggregateCommandHandler]
    public function handleInTrait(Command1 $command)
    {
        yield new Event4();
    }
}
