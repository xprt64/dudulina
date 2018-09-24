<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\CodeGeneration;

interface CodeGenerator
{
    public function generateClass(string $template, \Iterator $filesToSearchForHandlers): string;
}