<?php


namespace tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData;


use Gica\Cqrs\Event;
use Gica\Cqrs\ReadModel\ReadModelInterface;

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