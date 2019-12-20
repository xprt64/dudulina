<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Command\MetadataFactory;


use Dudulina\Command;
use Dudulina\Command\CommandWithMetadata;
use Dudulina\Command\MetadataWrapper;

class DefaultMetadataWrapper implements MetadataWrapper
{

    public function wrapCommandWithMetadata(Command $command, $metadata = null): CommandWithMetadata
    {
        return new CommandWithMetadata($command, $metadata);
    }
}