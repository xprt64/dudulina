<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);
namespace Dudulina\Query;

interface Answerer
{
    public function answer($answeredQuestion):void;
}