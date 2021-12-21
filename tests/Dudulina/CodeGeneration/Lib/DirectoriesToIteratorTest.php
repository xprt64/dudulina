<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\CodeGeneration\Lib;


class DirectoriesToIteratorTest extends \PHPUnit\Framework\TestCase
{
    public function testName()
    {
        $sut = new DirectoriesToIterator([__DIR__]);
        $this->assertTrue(true);
    }
}
