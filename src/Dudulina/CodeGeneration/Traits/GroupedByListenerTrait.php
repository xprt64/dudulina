<?php


namespace Dudulina\CodeGeneration\Traits;

use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByListenerMapCodeGenerator;
use Dudulina\CodeGeneration\CodeGenerator;

trait GroupedByListenerTrait
{
    use MapCodeGeneratorTrait;

    protected function getGenerator(): CodeGenerator
    {
        return new CodeGenerator(
            new GroupedByListenerMapCodeGenerator(),
            $this->fileSystem
        );
    }
}