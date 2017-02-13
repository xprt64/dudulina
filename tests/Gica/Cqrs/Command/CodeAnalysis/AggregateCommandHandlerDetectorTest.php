<?php


namespace tests\Gica\Cqrs\Command\CodeAnalysis;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CodeAnalysis\AggregateCommandHandlerDetector;


class AggregateCommandHandlerDetectorTest extends \PHPUnit_Framework_TestCase
{

    public function testAccepted()
    {

        $commandClass = new \ReflectionClass(Command1::class);
        $aggregateClass = new \ReflectionClass(SomeValidAggregate::class);

        $sut = new AggregateCommandHandlerDetector();

        $this->assertTrue($sut->isMessageClass($commandClass));

        $this->assertTrue($sut->isMethodAccepted($aggregateClass->getMethods()[0]));
    }

    public function testNotAccepted()
    {

        $commandClass = new \ReflectionClass(\stdClass::class);
        $aggregateClass = new \ReflectionClass(SomeInvalidAggregate::class);

        $sut = new AggregateCommandHandlerDetector();

        $this->assertFalse($sut->isMessageClass($commandClass));

        $this->assertFalse($sut->isMethodAccepted($aggregateClass->getMethods()[0]));
    }
}

class SomeValidAggregate
{
    public function handleCommand1(Command1 $command)
    {

    }
}

class Command1 implements Command
{

    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }
}

class SomeInvalidAggregate
{
    public function doCommand1(Command1 $command)
    {

    }
}
