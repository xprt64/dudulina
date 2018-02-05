<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Event;


use Dudulina\Command\CommandWithMetadata;

interface MetadataFactory
{
    public function factoryEventMetadata(CommandWithMetadata $command, $aggregate): MetaData;
}