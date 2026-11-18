<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Event\MetadataFactory;


use Dudulina\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Dudulina\Command\CommandWithMetadata;
use Dudulina\Event\MetaData;
use Dudulina\Event\MetadataFactory;

class DefaultMetadataFactory implements MetadataFactory
{

    public function __construct(private ?AuthenticatedIdentityReaderService $identityReaderService = null)
    {
    }

    public function factoryEventMetadata(CommandWithMetadata $command, $aggregate): MetaData
    {
        return new MetaData(
            $command->getCommand()->getAggregateId(),
            $aggregate::class,
            new \DateTimeImmutable(),
            $this->identityReaderService ? $this->identityReaderService->getAuthenticatedIdentityId() : null,
            $command->getMetadata()
        );
    }
}