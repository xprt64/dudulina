<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);
namespace Dudulina\Query;

use Dudulina\Query\Answerer\MethodDescriptor;

interface AskerResolver
{
    /**
     * @param $question
     * @return MethodDescriptor[]
     */
    public function findAskers($question):array;

    public function findAskerNotifyMethod($asker, $question): MethodDescriptor;
}