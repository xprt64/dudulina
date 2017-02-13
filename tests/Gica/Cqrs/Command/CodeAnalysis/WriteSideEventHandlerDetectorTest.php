<?php


namespace tests\Gica\Cqrs\Command\CodeAnalysis\WriteSideEventHandlerDetectorTest;


use Gica\Cqrs\Command\CodeAnalysis\WriteSideEventHandlerDetector;
use Gica\Cqrs\Event;


class WriteSideEventHandlerDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testAccepted()
    {
        $commandClass = new \ReflectionClass(Event1::class);
        $validatorClass = new \ReflectionClass(SomeValidReadModel::class);

        $sut = new WriteSideEventHandlerDetector();

        $this->assertTrue($sut->isMessageClass($commandClass));

        $this->assertTrue($sut->isMethodAccepted($validatorClass->getMethods()[0]));
    }

    public function testNotAccepted()
    {
        $commandClass = new \ReflectionClass(\stdClass::class);
        $validatorClass = new \ReflectionClass(SomeInvalidReadModel::class);

        $sut = new WriteSideEventHandlerDetector();

        $this->assertFalse($sut->isMessageClass($commandClass));

        $this->assertFalse($sut->isMethodAccepted($validatorClass->getMethods()[0]));
    }
}


class SomeValidReadModel
{
    public function processEvent1(Event1 $command)
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
