<?php

namespace tests\Dudulina\Doubles;

/**
 * Aggregate double whose handler/apply method names are only known at runtime (PHPUnit cannot mock such methods).
 * Mimics the subset of the PHPUnit mock API used by the tests: method()->with()->willReturn()/willThrowException().
 */
class DynamicAggregate
{
    /** @var array<string, array{args: array, return: mixed, throw: ?\Throwable}> */
    private array $stubs = [];

    public function method(string $name): DynamicAggregateStub
    {
        return new DynamicAggregateStub($this->stubs, $name);
    }

    public function __call(string $name, array $arguments)
    {
        if (!isset($this->stubs[$name])) {
            return null; // like a PHPUnit mock: unconfigured calls do nothing
        }
        $stub = $this->stubs[$name];
        if ($stub['args'] != array_slice($arguments, 0, count($stub['args']))) {
            throw new \InvalidArgumentException("Unexpected arguments for $name");
        }
        if ($stub['throw']) {
            throw $stub['throw'];
        }

        return $stub['return'];
    }
}
