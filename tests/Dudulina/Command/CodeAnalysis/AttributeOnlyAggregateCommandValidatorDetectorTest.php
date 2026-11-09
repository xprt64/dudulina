<?php

namespace tests\Dudulina\CodeGeneration\Command\AttributeOnlyAggregateCommandValidatorDetectorTest;

use Dudulina\Attributes\AggregateCommandValidator;
use Dudulina\CodeGeneration\Command\AttributeOnlyAggregateCommandValidatorDetector;
use Dudulina\Command;

class AttributeOnlyAggregateCommandValidatorDetectorTest extends \PHPUnit\Framework\TestCase
{
    public function testAcceptsACommandClass()
    {
        $sut = new AttributeOnlyAggregateCommandValidatorDetector();

        $this->assertTrue($sut->isMessageClass(new \ReflectionClass(Command1::class)));
        $this->assertFalse($sut->isMessageClass(new \ReflectionClass(\stdClass::class)));
    }

    public function testAcceptsOnlyMethodsWithTheAttribute()
    {
        $sut = new AttributeOnlyAggregateCommandValidatorDetector();
        $class = new \ReflectionClass(SomeValidators::class);

        $this->assertTrue($sut->isMethodAccepted($class->getMethod('anyNameWithAttribute')));
        $this->assertFalse($sut->isMethodAccepted($class->getMethod('validateByNameOnly')), 'the validate* name convention must not be accepted');
        $this->assertFalse($sut->isMethodAccepted($class->getMethod('validateByDocblock')), 'the docblock must not be accepted');
    }
}

class SomeValidators
{
    #[AggregateCommandValidator]
    public function anyNameWithAttribute(Command1 $command)
    {
    }

    public function validateByNameOnly(Command1 $command)
    {
    }

    /** @AggregateCommandValidator */
    public function validateByDocblock(Command1 $command)
    {
    }
}

class Command1 implements Command
{
    public function getAggregateId()
    {
    }
}
