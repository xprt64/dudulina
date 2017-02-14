<?php


namespace tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData;


use Gica\Cqrs\Event;
use Gica\Cqrs\ReadModel\ReadModelInterface;

class ReadModel2 implements ReadModelInterface
{

    public function clearModel()
    {
    }

    public function createModel()
    {
    }

    public function onEvent2(Event2 $event)
    {

    }
}

class Event2 implements Event
{

}