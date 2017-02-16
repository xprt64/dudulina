<?php


namespace tests\Gica\Cqrs\Aggregate\CodeAnalysis\ListenerClassValidator;


use Gica\Cqrs\Aggregate\CodeAnalysis\ListenerClassValidator\OnlyAggregateByName;


class OnlyAggregateByNameTest extends \PHPUnit_Framework_TestCase
{

    public function testOnlyAggregateByNameTrue()
    {
        $sut = new OnlyAggregateByName();

        $acceptedClass = new \ReflectionClass(SomeAggregate::class);

        $this->assertTrue($sut->isClassAccepted($acceptedClass));
    }

    public function testOnlyAggregateByNameFalse()
    {
        $sut = new OnlyAggregateByName();

        $acceptedClass = new \ReflectionClass(SomeAggregateWithInvalidName::class);

        $this->assertFalse($sut->isClassAccepted($acceptedClass));
    }
}

class SomeAggregate
{

}

class SomeAggregateWithInvalidName
{

}