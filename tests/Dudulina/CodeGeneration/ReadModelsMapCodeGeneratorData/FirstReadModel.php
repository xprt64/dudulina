<?php


namespace tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData;


use Dudulina\ReadModel\ReadModelInterface;
use tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event1;

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