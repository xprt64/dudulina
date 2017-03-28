<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Event;


use Gica\Cqrs\Command\CommandWithMetadata;

interface MetadataFactory
{
    public function factoryEventMetadata(CommandWithMetadata $command, $aggregate): MetaData;
}