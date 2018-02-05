<?php


namespace tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData;


use Dudulina\Event;
use Dudulina\ReadModel\ReadModelInterface;

class Saga1 implements ReadModelInterface
{

    public function clearModel()
    {
    }

    public function createModel()
    {
    }

    public function processEvent1(Event1 $event)
    {

    }
}

class Event1 implements Event
{

}