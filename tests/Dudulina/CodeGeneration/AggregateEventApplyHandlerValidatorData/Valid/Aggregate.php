<?php


namespace tests\Dudulina\CodeGeneration\AggregateEventApplyHandlerValidatorData\Valid;


use Dudulina\Event;

class Aggregate
{
    public function applyEvent1(Event1 $event)
    {

    }

    public function someOtherMethod()
    {
    }
}

class Event1 implements Event
{

}