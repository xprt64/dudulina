<?php


namespace tests\Dudulina\CodeGeneration\SagaEventListenerMapCodeGeneratorData;


use Dudulina\Event;
use Dudulina\ReadModel\ReadModelInterface;

class Saga2 implements ReadModelInterface
{

    public function clearModel()
    {
    }

    public function createModel()
    {
    }

    public function processEvent2(Event2 $event)
    {

    }
}

class Event2 implements Event
{

}