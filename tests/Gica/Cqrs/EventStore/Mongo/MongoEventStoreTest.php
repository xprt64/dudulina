<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\unit\Gica\Cqrs\EventStore\Mongo;


class MongoEventStoreTest extends \PHPUnit_Framework_TestCase
{

    public function test_appendEventsForAggregate()
    {
        $databaseName = 'cqrs';

        $client = new \MongoDB\Client('mongodb://testusername:testpasswd@localhost:27017/' . $databaseName);

        $db = $client->selectDatabase($databaseName);

        $collection = $db->selectCollection('eventStore');

        $eventStore = new \Gica\Cqrs\EventStore\Mongo\MongoEventStore($collection, new \Gica\Cqrs\EventStore\Mongo\EventSerializer());

        $eventStore->dropStore();
        $eventStore->createStore();

        $aggregateId = new \Gica\Types\Guid();
        $aggregateClass = 'aggClass';

        $events = [new Event1(11), new Event2(22)];

        $eventStore->appendEventsForAggregate($aggregateId, $aggregateClass, $events, -1, 0);

        $this->assertCount(1, $collection->find()->toArray());

        $stream = $eventStore->loadEventsForAggregate($aggregateClass, $aggregateId);

        $events = iterator_to_array($stream->getIterator());

        $this->assertCount(2, $events);

        $this->assertInstanceOf(Event1::class, $events[0]);
        $this->assertInstanceOf(Event2::class, $events[1]);
    }

    /**
     * @expectedException \Gica\Cqrs\EventStore\Exception\ConcurrentModificationException
     */
    public function test_appendEventsForAggregateShouldNotWriteTwiceTheSameEvents()
    {
        $databaseName = 'cqrs';

        $client = new \MongoDB\Client('mongodb://testusername:testpasswd@localhost:27017/' . $databaseName);

        $db = $client->selectDatabase($databaseName);

        $collection = $db->selectCollection('eventStore');

        $eventStore = new \Gica\Cqrs\EventStore\Mongo\MongoEventStore($collection, new \Gica\Cqrs\EventStore\Mongo\EventSerializer());

        $eventStore->dropStore();
        $eventStore->createStore();

        $aggregateId = new \Gica\Types\Guid();

        $events = [new Event1(11), new Event2(22)];

        $eventStore->appendEventsForAggregate($aggregateId, $events, -1);

        $eventStore->appendEventsForAggregate($aggregateId, $events, -1);//should fail
    }
}

class Event1 implements \Gica\Cqrs\Event
{
    private $field1;

    public function __construct($field1)
    {
        $this->field1 = $field1;
    }

    /**
     * @return mixed
     */
    public function getField1()
    {
        return $this->field1;
    }


}

class Event2 implements \Gica\Cqrs\Event
{
    private $field2;

    public function __construct($field2)
    {
        $this->field2 = $field2;
    }

    /**
     * @return mixed
     */
    public function getField2()
    {
        return $this->field2;
    }

}
