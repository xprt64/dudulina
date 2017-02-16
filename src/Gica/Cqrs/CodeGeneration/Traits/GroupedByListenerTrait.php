<?php


namespace Gica\Cqrs\CodeGeneration\Traits;

use Gica\CodeAnalysis\MethodListenerDiscovery\MapCodeGenerator\GroupedByListenerMapCodeGenerator;
use Gica\Cqrs\CodeGeneration\CodeGenerator;

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