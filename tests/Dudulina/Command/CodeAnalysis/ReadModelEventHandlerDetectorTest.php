<?php


namespace tests\Dudulina\CodeGeneration\Command;


use Dudulina\CodeGeneration\Command\ReadModelEventHandlerDetector;
use Dudulina\Event;


class ReadModelEventHandlerDetectorTest extends \PHPUnit\Framework\TestCase
{

    public function testAccepted()
    {
        $commandClass = new \ReflectionClass(Event1::class);
        $validatorClass = new \ReflectionClass(SomeValidReadModel::class);

        $sut = new ReadModelEventHandlerDetector();

        $this->assertTrue($sut->isMessageClass($commandClass));

        $this->assertTrue($sut->isMethodAccepted($validatorClass->getMethods()[0]));
    }

    public function testNotAccepted()
    {
        $commandClass = new \ReflectionClass(\stdClass::class);
        $validatorClass = new \ReflectionClass(SomeInvalidReadModel::class);

        $sut = new ReadModelEventHandlerDetector();

        $this->assertFalse($sut->isMessageClass($commandClass));

        $this->assertFalse($sut->isMethodAccepted($validatorClass->getMethods()[0]));
    }
}


class SomeValidReadModel
{
    public function onEvent1(Event1 $command)
    {

    }
}

class Event1 implements Event
{
}

class SomeInvalidReadModel
{
    public function doEvent1(Event1 $command)
    {

    }
}
