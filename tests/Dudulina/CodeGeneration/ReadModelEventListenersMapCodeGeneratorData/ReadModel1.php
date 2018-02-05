<?php


namespace tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData;


use Dudulina\Event;
use Dudulina\ReadModel\ReadModelInterface;

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