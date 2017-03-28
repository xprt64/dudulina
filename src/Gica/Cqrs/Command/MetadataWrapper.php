<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Command;


use Gica\Cqrs\Command;

interface MetadataWrapper
{
    public function wrapCommandWithMetadata(Command $command, $metadata): CommandWithMetadata;
}