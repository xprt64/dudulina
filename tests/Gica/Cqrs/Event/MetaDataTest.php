<?php


namespace tests\Gica\Cqrs\Event;


use Gica\Cqrs\Event\MetaData;


class MetaDataTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $dateCreated = new \DateTimeImmutable();

        $metaData = new MetaData(
            123,
            'aggregateclass',
            $dateCreated,
            345
        );

        $this->assertEquals(123, $metaData->getAggregateId());
        $this->assertEquals('aggregateclass', $metaData->getAggregateClass());
        $this->assertSame($dateCreated, $metaData->getDateCreated());
        $this->assertSame(345, $metaData->getAuthenticatedUserId());
    }
}
