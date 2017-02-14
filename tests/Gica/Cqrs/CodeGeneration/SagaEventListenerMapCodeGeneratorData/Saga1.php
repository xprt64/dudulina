<?php


namespace tests\Gica\Cqrs\CodeGeneration\SagaEventListenerMapCodeGeneratorData;


use Gica\Cqrs\Event;
use Gica\Cqrs\ReadModel\ReadModelInterface;

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