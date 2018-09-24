<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\CodeGeneration\Lib;

class DirectoriesToIterator extends \AppendIterator
{
    public function __construct(array $directories)
    {
        parent::__construct();
        foreach ($directories as $folder) {
            $this->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)));
        }
    }
}