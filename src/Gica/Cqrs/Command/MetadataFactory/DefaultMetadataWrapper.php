<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command\MetadataFactory;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandMetadata;
use Gica\Cqrs\Command\CommandWithMetadata;
use Gica\Cqrs\Command\MetadataWrapper;

class DefaultMetadataWrapper implements MetadataWrapper
{

    public function wrapCommandWithMetadata(Command $command, CommandMetadata $metadata = null): CommandWithMetadata
    {
        return new CommandWithMetadata($command, $metadata);
    }
}