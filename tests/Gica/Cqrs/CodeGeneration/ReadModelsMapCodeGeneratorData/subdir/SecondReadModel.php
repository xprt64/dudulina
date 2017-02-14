<?php


namespace tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\subdir;


use Gica\Cqrs\ReadModel\ReadModelInterface;
use tests\Gica\Cqrs\CodeGeneration\ReadModelsMapCodeGeneratorData\Events\Event2;

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