<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);
namespace Dudulina\Query;

use Dudulina\Query\Answerer\MethodDescriptor;

interface AnswererResolver
{
    public function findAnswerer($question):MethodDescriptor;
}