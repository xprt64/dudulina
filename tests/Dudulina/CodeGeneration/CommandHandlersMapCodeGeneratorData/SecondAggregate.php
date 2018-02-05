<?php


namespace tests\Dudulina\CodeGeneration\CommandHandlersMapCodeGeneratorData;


use Dudulina\Command;

class SecondAggregate
{
    public function handleCommand3(Command3 $command)
    {

    }

    public function handleCommand4(Command4 $command)
    {

    }

    public function someMethod()
    {

    }
}

class Command3 implements Command
{

    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }
}

class Command4 implements Command
{

    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }
}