<?php


namespace Dudulina\CodeGeneration\Traits;

use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByEventMapCodeGenerator;
use Dudulina\CodeGeneration\CodeGenerator;

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