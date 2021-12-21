<?php


namespace tests\Dudulina\Event;


use Dudulina\Event\MetaData;
use Dudulina\Testing\EventStore\InMemory\InMemoryEventSequence;


class MetaDataTest extends \PHPUnit\Framework\TestCase
{

    public function test()
    {
        $dateCreated = new \DateTimeImmutable();

        $commandMetadata = new \stdClass();

        $metaData = new MetaData(
            123,
            'aggregateclass',
            $dateCreated,
            345,
            $commandMetadata
        );

        $this->assertEquals(123, $metaData->getAggregateId());
        $this->assertEquals('aggregateclass', $metaData->getAggregateClass());
        $this->assertSame($dateCreated, $metaData->getDateCreated());
        $this->assertSame(345, $metaData->getAuthenticatedUserId());
        $this->assertSame($commandMetadata, $metaData->getCommandMetadata());

        $metaData2 = $metaData->withEventId('234');
        $this->assertSame('234', $metaData2->getEventId());

        $ts = new InMemoryEventSequence(1, 2);
        $metaData3 = $metaData->withSequence($ts);
        $this->assertSame($ts, $metaData3->getSequence());
    }
}
