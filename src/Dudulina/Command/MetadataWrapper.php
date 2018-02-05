<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command;


use Dudulina\Command;

interface MetadataWrapper
{
    public function wrapCommandWithMetadata(Command $command, CommandMetadata $metadata = null): CommandWithMetadata;
}