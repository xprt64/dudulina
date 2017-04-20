<?php


namespace tests\Gica\Cqrs\Event;


use Gica\Cqrs\Event\MetaData;
use Gica\Types\Guid;


class MetaDataTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $dateCreated = new \DateTimeImmutable();

        $metaData = new MetaData(
            123,
            'aggregateclass',
            Guid::generate(),
            $dateCreated,
            345,
            'commandMetadata'
        );

        $this->assertEquals(123, $metaData->getAggregateId());
        $this->assertEquals('aggregateclass', $metaData->getAggregateClass());
        $this->assertSame($dateCreated, $metaData->getDateCreated());
        $this->assertSame(345, $metaData->getAuthenticatedUserId());
        $this->assertSame('commandMetadata', $metaData->getCommandMetadata());
    }
}
