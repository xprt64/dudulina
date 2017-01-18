<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;


class CommandApplier
{
    public function applyCommand($aggregate, \Gica\Cqrs\Command $command, $methodName)
    {
        $generator = call_user_func([$aggregate, $methodName], $command);

        yield from $generator;
    }
}