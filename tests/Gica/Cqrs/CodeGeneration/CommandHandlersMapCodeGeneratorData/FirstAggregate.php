<?php


namespace tests\Gica\Cqrs\CodeGeneration\CommandHandlersMapCodeGeneratorData;


use Gica\Cqrs\Command;

class FirstAggregate
{
    public function handleCommand1(Command1 $command)
    {

    }

    public function handleCommand2(Command2 $command)
    {

    }

    public function someMethod()
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

class Command2 implements Command
{

    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }
}