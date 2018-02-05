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

    /**
     * @var AuthenticatedIdentityReaderService
     */
    private $identityReaderService;

    public function __construct(
        ?AuthenticatedIdentityReaderService $identityReaderService = null
    )
    {
        $this->identityReaderService = $identityReaderService;
    }

    public function factoryEventMetadata(CommandWithMetadata $command, $aggregate): MetaData
    {
        return new MetaData(
            $command->getCommand()->getAggregateId(),
            get_class($aggregate),
            new \DateTimeImmutable(),
            $this->identityReaderService ? $this->identityReaderService->getAuthenticatedIdentityId() : null,
            $command->getMetadata()
        );
    }
}