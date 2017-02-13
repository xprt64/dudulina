<?php


namespace tests\Gica\Cqrs\Command\CodeAnalysis;


use Gica\Cqrs\Command\CodeAnalysis\ReadModelEventHandlerDetector;
use Gica\Cqrs\Event;


class ReadModelEventHandlerDetectorTest extends \PHPUnit_Framework_TestCase
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
