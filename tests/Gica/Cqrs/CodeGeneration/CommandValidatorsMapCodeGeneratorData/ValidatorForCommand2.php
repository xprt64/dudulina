<?php


namespace tests\Gica\Cqrs\CodeGeneration\CommandValidatorsMapCodeGeneratorData;


use Gica\Cqrs\Command;

class ValidatorForCommand2
{
    public function validateCommand2(Command2 $command)
    {

    }
}

class Command2 implements Command
{

    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }
}