<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);

namespace Dudulina\Query\AskerResolver;

use Dudulina\Query\Answerer\MethodDescriptor;

class ByMap implements \Dudulina\Query\AskerResolver
{
    /**
     * @param \Dudulina\Query\Answerer\MethodDescriptor[][] $map
     */
    public function __construct(
        /** @var MethodDescriptor[][] */
        private $map
    )
    {
    }

    /**
     * @param $question
     * @return MethodDescriptor[]
     */
    #[\Override]
    public function findAskers($question): array
    {
        $questionClass = $question::class;
        if (!isset($this->map[$questionClass])) {
            return [];
        }
        return array_map(fn($row) => new MethodDescriptor($row[0], $row[1]), $this->map[$questionClass]);
    }

    #[\Override]
    public function findAskerNotifyMethod($asker, $question): MethodDescriptor
    {
        $needleClass = $asker::class;
        $askers = $this->findAskers($question);
        foreach ($askers as $askerFound) {
            if ($askerFound->getHandlerClass() === $needleClass) {
                return $askerFound;
            }
        }
        throw new \InvalidArgumentException("There is no asker $needleClass");
    }
}