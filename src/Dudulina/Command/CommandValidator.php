<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\Command;

use Dudulina\Command;

interface CommandValidator
{

    public function validateCommand(Command $command);
}