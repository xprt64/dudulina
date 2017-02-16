<?php


namespace tests\Gica\Cqrs\CodeGeneration;


use Gica\Cqrs\CodeGeneration\AggregateEventApplyHandlerValidator;
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

        $sut->validate(__DIR__ . '/AggregateEventApplyHandlerValidatorData/Valid');
    }
}
