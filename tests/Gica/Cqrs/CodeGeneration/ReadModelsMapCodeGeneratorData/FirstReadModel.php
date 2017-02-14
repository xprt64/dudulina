<?php


namespace tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData;


use Gica\Cqrs\ReadModel\ReadModelInterface;
use tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event1;

class FirstReadModel implements ReadModelInterface
{
    public function onEvent1(Event1 $event)
    {

    }

    public function clearModel()
    {
        // TODO: Implement clearModel() method.
    }

    public function createModel()
    {
        // TODO: Implement createModel() method.
    }
}