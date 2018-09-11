<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);

namespace Dudulina\Query;

interface Asker
{
    public function askAndReturn($question);
    public function askAndNotifyAsker($question, $asker):void;
}