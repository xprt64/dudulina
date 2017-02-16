<?php


namespace Gica\Cqrs\CodeGeneration\Traits;

use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;
use Gica\Cqrs\CodeGeneration\CodeGenerator;

trait GroupedByEventTrait
{
    use MapCodeGeneratorTrait;

    protected function getGenerator(): CodeGenerator
    {
        return new CodeGenerator(
            new GroupedByEventMapCodeGenerator(),
            $this->fileSystem
        );
    }
}