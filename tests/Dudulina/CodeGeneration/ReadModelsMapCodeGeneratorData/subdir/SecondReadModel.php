<?php


namespace tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\subdir;


use Dudulina\ReadModel\ReadModelInterface;
use tests\Dudulina\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event2;

class SecondReadModel implements ReadModelInterface
{
    public function onEvent2(Event2 $event)
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