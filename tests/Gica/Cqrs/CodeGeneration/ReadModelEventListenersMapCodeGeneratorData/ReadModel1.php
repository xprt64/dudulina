<?php


namespace tests\Gica\Cqrs\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData;


use Gica\Cqrs\Event;
use Gica\Cqrs\ReadModel\ReadModelInterface;

class ReadModel1 implements ReadModelInterface
{

    public function clearModel()
    {
    }

    public function createModel()
    {
    }

    public function onEvent1(Event1 $event)
    {

    }
}

class Event1 implements Event
{

}