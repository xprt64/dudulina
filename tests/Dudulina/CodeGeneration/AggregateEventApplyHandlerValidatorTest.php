<?php


namespace tests\Dudulina\CodeGeneration;


use Dudulina\CodeGeneration\AggregateEventApplyHandlerValidator;
use Psr\Log\LoggerInterface;


class AggregateEventApplyHandlerValidatorTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $sut = new AggregateEventApplyHandlerValidator(
            $logger
        );

        $sut->validate(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/AggregateEventApplyHandlerValidatorData/Valid')));
    }
}
