<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);

namespace Dudulina\Query\AskerResolver;

use Dudulina\Query\Answerer\MethodDescriptor;

class ByMap implements \Dudulina\Query\AskerResolver
{
    /** @var MethodDescriptor[][] */
    private $map;

    public function __construct($map)
    {
        $this->map = $map;
    }

    /**
     * @param $question
     * @return MethodDescriptor[]
     */
    public function findAskers($question): array
    {
        $questionClass = \get_class($question);
        if (!isset($this->map[$questionClass])) {
            return [];
        }
        return array_map(function ($row) {
            return new MethodDescriptor($row[0], $row[1]);
        }, $this->map[$questionClass]);
    }

    public function findAskerNotifyMethod($asker, $question): MethodDescriptor
    {
        $needleClass = \get_class($asker);
        $askers = $this->findAskers($question);
        foreach ($askers as $askerFound) {
            if ($askerFound->getHandlerClass() === $needleClass) {
                return $askerFound;
            }
        }
        throw new \InvalidArgumentException("There is no asker $needleClass");
    }
}