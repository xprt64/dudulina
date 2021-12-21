<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\AggregateEventApplyHandlerValidator;
use Psr\Log\LoggerInterface;


class AggregateEventApplyHandlerValidatorTest extends \PHPUnit\Framework\TestCase
{

    public function test()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $sut = new AggregateEventApplyHandlerValidator(
            $logger
        );

        $sut->validate(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/AggregateEventApplyHandlerValidatorData/Valid')));

        $this->assertTrue(true);
    }
}
