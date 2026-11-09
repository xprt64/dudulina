<?php

namespace tests\Dudulina\CodeGeneration\YieldedEventsFinderData;

use Dudulina\Attributes\AggregateCommandHandler;

class UnresolvableAggregate
{
    private array $events = [];
    private $other;

    #[AggregateCommandHandler]
    public function handleUnresolvable(Command1 $command)
    {
        yield $this->untypedFactory();
        yield from $this->other->events();
        yield $this->events[0];
        yield $this->interfaceTypedFactory();
        yield (new Event2())->describe();
        yield;
        yield new Event1();
    }

    private function untypedFactory()
    {
        return new Event1();
    }

    private function interfaceTypedFactory(): \Dudulina\Event
    {
        return new Event1();
    }
}
