<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);

namespace Dudulina\Query\AnswererResolver;

use Dudulina\Query\Answerer\MethodDescriptor;

class ByMap implements \Dudulina\Query\AnswererResolver
{
    /** @var MethodDescriptor[] */
    private $map;

    public function __construct($map)
    {
        $this->map = $map;
    }

    public function findAnswerer($question): MethodDescriptor
    {
        $questionClass = \get_class($question);
        if (!isset($this->map[$questionClass])) {
            throw new \InvalidArgumentException("There is no answerer for question {$questionClass}");
        }
        $row = reset($this->map[$questionClass]);
        return new MethodDescriptor($row[0], $row[1]);
    }
}