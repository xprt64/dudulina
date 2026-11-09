<?php

namespace tests\Dudulina\CodeGeneration\YieldedEventsFinderData;

use Dudulina\Event;

class Event2 implements Event
{
    public function withSomething(int $x): static
    {
        return clone $this;
    }

    public function withOther(): ?Event2
    {
        return $this;
    }

    public function describe(): string
    {
        return 'not an event';
    }
}
