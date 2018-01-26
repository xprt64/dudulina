<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Command;

use Gica\Cqrs\Command\CommandMetadata;
use Gica\Types\Guid;

class CommandMetadataTest extends \PHPUnit_Framework_TestCase
{

    public function test_commandId()
    {
        $commandId = Guid::generate();

        $metadata = (new CommandMetadata())->withoutCommandId()->withCommandId($commandId);

        $this->assertSame($commandId, $metadata->getCommandId());
    }

    public function test_correlationId()
    {
        $correlationId = Guid::generate();

        $metadata = (new CommandMetadata())->withoutCorrelationId()->withCorrelationId($correlationId);

        $this->assertSame($correlationId, $metadata->getCorrelationId());
    }
}
