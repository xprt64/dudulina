<?php


namespace tests\Dudulina\CodeGeneration\Command;


use Dudulina\Command;
use Dudulina\CodeGeneration\Command\AggregateCommandHandlerDetector;


class AggregateCommandHandlerDetectorTest extends \PHPUnit\Framework\TestCase
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
