<?php

namespace tests\Dudulina\Doubles;

class DynamicAggregateStub
{
    public function __construct(private array &$stubs, private string $name)
    {
        $this->stubs[$name] ??= ['args' => [], 'return' => null, 'throw' => null];
    }

    public function with(...$args): self
    {
        $this->stubs[$this->name]['args'] = $args;

        return $this;
    }

    public function willReturn($value): self
    {
        $this->stubs[$this->name]['return'] = $value;

        return $this;
    }

    public function willThrowException(\Throwable $e): self
    {
        $this->stubs[$this->name]['throw'] = $e;

        return $this;
    }
}
