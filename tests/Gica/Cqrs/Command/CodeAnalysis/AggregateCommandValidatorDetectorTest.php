<?php


namespace tests\Gica\Cqrs\Command\CodeAnalysis\AggregateCommandValidatorDetectorTest;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CodeAnalysis\AggregateCommandValidatorDetector;


class AggregateCommandValidatorDetectorTest extends \PHPUnit_Framework_TestCase
{

    public function testAccepted()
    {

        $commandClass = new \ReflectionClass(Command1::class);
        $validatorClass = new \ReflectionClass(SomeValidValidator::class);

        $sut = new AggregateCommandValidatorDetector();

        $this->assertTrue($sut->isMessageClass($commandClass));

        $this->assertTrue($sut->isMethodAccepted($validatorClass->getMethods()[0]));
    }

    public function testNotAccepted()
    {

        $commandClass = new \ReflectionClass(\stdClass::class);
        $validatorClass = new \ReflectionClass(SomeInvalidValidator::class);

        $sut = new AggregateCommandValidatorDetector();

        $this->assertFalse($sut->isMessageClass($commandClass));

        $this->assertFalse($sut->isMethodAccepted($validatorClass->getMethods()[0]));
    }
}


class SomeValidValidator
{
    public function validateCommand1(Command1 $command)
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

class SomeInvalidValidator
{
    public function doCommand1(Command1 $command)
    {

    }
}
