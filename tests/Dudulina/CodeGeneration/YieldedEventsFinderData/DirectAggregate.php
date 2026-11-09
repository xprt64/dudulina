<?php

namespace tests\Dudulina\CodeGeneration\YieldedEventsFinderData;

use Dudulina\Attributes\AggregateCommandHandler;

class DirectAggregate
{
    #[AggregateCommandHandler]
    public function handleDirect(Command1 $command)
    {
        yield new Event1();
        yield (new Event2())->withSomething(1);
        yield from $this->helper();
        $event = new Event4();
        yield $event;
        yield $this->createEvent5();
        yield self::createEvent6();
        $callback = function () {
            yield new Event7(); // a closure is another generator: its yields are not the handler's
        };
    }

    private function helper()
    {
        yield new Event3();
    }

    private function createEvent5(): Event5
    {
        return new Event5();
    }

    private static function createEvent6(): Event6
    {
        return new Event6();
    }

    public function notAHandler(Command1 $command)
    {
        yield new Event7();
    }
}
