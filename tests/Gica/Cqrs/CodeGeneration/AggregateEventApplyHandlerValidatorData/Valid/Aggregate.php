<?php


namespace tests\Gica\Cqrs\CodeGeneration\AggregateEventApplyHandlerValidatorData\Valid;


use Gica\Cqrs\Event;

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