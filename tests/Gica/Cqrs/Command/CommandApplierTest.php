<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/


namespace tests\Gica\Cqrs\Command;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandApplier;


class CommandApplierTest extends \PHPUnit_Framework_TestCase
{

    public function testApplyCommand()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $event = new \stdClass();
        $event->someProperty = 'someValue';

        $aggregate = new MyAggregate($event);

        $sut = new CommandApplier();

        $events = iterator_to_array($sut->applyCommand($aggregate, $command, 'applySomeCommand'));

        $this->assertCount(1, $events);
        $this->assertSame($event, $events[0]);
    }
}

class MyAggregate
{

    private $eventToBeYielded;
    private $command;

    public function __construct($eventToBeYielded)
    {
        $this->eventToBeYielded = $eventToBeYielded;
    }

    public function applySomeCommand($command)
    {
        $this->command = $command;
        yield $this->eventToBeYielded;
    }
}