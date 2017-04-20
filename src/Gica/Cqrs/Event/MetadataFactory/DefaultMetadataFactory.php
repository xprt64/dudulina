<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Event\MetadataFactory;


use Gica\Cqrs\Command\CommandDispatcher\AuthenticatedIdentityReaderService;
use Gica\Cqrs\Command\CommandWithMetadata;
use Gica\Cqrs\Event\MetaData;
use Gica\Cqrs\Event\MetadataFactory;
use Gica\Types\Guid;

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