<?php

namespace tests\Dudulina\Doubles;

/**
 * Concrete class with the callback methods the tests need to mock (PHPUnit cannot mock methods that do not exist).
 */
class TestListener
{
    public function onEvent1(...$args)
    {
    }

    public function validateTheCommand(...$args)
    {
    }

    public function someListenerMethod(...$args)
    {
    }
}
