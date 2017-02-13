<?php


namespace tests\Gica\Cqrs\Command\CommandDispatcher;


use Gica\Cqrs\Command\CommandDispatcher\ConcurrentProofFunctionCaller;
use Gica\Cqrs\Command\Exception\TooManyCommandExecutionRetries;
use Gica\Cqrs\EventStore\Exception\ConcurrentModificationException;


class ConcurrentProofFunctionCallerTest extends \PHPUnit_Framework_TestCase
{

    public function testExecuteFunctionWithNoConcurency()
    {
        $invocations = 0;
        $argument1 = null;
        $argument2 = null;
        $pureFunction = function ($argument1function, $argument2function) use (&$invocations, &$argument1, &$argument2) {
            $invocations++;
            $argument1 = $argument1function;
            $argument2 = $argument2function;
        };

        $sut = new ConcurrentProofFunctionCaller();

        $sut->executeFunction($pureFunction, 1, [100, 200]);

        $this->assertSame(1, $invocations);
        $this->assertSame(100, $argument1);
        $this->assertSame(200, $argument2);

    }

    public function testExecuteFunctionWithConcurrency()
    {
        $invocations = 0;
        $pureFunction = function () use (&$invocations) {
            $invocations++;
            if (1 === $invocations) {
                throw new ConcurrentModificationException();
            }
        };

        $sut = new ConcurrentProofFunctionCaller();

        $sut->executeFunction($pureFunction, 999, [100, 200]);

        $this->assertSame(2, $invocations);

    }

    public function testExecuteFunctionWithMaximumRetriesReached()
    {
        $invocations = 0;
        $pureFunction = function () use (&$invocations) {
            $invocations++;
            throw new ConcurrentModificationException();
        };

        $sut = new ConcurrentProofFunctionCaller();

        try {
            $sut->executeFunction($pureFunction, 10);
            $this->fail("TooManyCommandExecutionRetries must be thrown");
        } catch (\Throwable $exception) {
            if (!$exception instanceof TooManyCommandExecutionRetries) {
                $this->fail("TooManyCommandExecutionRetries must be thrown");
            }
            $this->assertSame(10 + 1, $invocations);
        }
    }
}
