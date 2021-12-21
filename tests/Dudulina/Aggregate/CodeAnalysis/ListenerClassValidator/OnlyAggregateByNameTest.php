<?php


namespace tests\Dudulina\Aggregate\CodeAnalysis\ListenerClassValidator;


use Dudulina\CodeGeneration\Aggregate\ListenerClassValidator\OnlyAggregateByName;
use PHPUnit\Framework\TestCase;

class OnlyAggregateByNameTest extends TestCase
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