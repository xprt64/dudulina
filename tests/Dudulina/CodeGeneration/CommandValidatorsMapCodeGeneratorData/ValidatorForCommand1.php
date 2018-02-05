<?php


namespace tests\Dudulina\CodeGeneration\CommandValidatorsMapCodeGeneratorData;


use Dudulina\Command;

class ValidatorForCommand1
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