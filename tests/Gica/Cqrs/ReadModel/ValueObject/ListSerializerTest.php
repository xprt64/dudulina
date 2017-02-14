<?php


namespace tests\Gica\Cqrs\ReadModel\ValueObject;


use Gica\Cqrs\ReadModel\ValueObject\ListSerializer;
use Gica\Types\SerializableInterface;


class ListSerializerTest extends \PHPUnit_Framework_TestCase
{

    public function test_serializeList()
    {
        $object = $this->getMockBuilder(SerializableInterface::class)
            ->getMock();

        $object->expects($this->once())
            ->method('serialize')
            ->willReturn('a');

        $result = ListSerializer::serializeList([$object]);

        $this->assertEquals(['a'], $result);
    }

    public function test_deserializeList()
    {
        /** @var SomeObject[] $result */
        $result = ListSerializer::deserializeList(SomeObject::class, ['a', 'b']);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(SomeObject::class, $result[0]);
        $this->assertInstanceOf(SomeObject::class, $result[1]);

        $this->assertSame('a', $result[0]->document);
        $this->assertSame('b', $result[1]->document);
    }

    /**
     * @param $data
     * @dataProvider dataForEmptyList
     */
    public function test_deserializeEmptyList($data)
    {
        $result = ListSerializer::deserializeList(SomeObject::class, $data);

        $this->assertCount(0, $result);
        $this->assertInternalType('array', $result);
    }

    public function dataForEmptyList()
    {
        return [
            [[]],
            [null],
            [new \ArrayIterator([])],
        ];
    }
}

class SomeObject
{
    public $document;

    public function __construct($document)
    {
        $this->document = $document;
    }

    public static function deserialize($document)
    {
        return new SomeObject($document);
    }
}