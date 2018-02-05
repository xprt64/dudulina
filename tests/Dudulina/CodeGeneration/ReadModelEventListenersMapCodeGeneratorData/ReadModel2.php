<?php


namespace tests\Dudulina\CodeGeneration\ReadModelEventListenersMapCodeGeneratorData;


use Dudulina\Event;
use Dudulina\ReadModel\ReadModelInterface;

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