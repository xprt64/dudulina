<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Command\CommandTesterWithExplanation\CommandDispatcherInvalidCommandTest;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandTester;
use Gica\Cqrs\Command\CommandTester\CommandTesterWithValidator;
use Gica\Cqrs\Command\CommandTesterWithExplanation;
use Gica\Cqrs\Command\CommandValidator;

class CommandTesterWithValidatorWithExplanationTest extends \PHPUnit_Framework_TestCase
{

    const AGGREGATE_ID = 123;

    /** @var Command */
    private $command;

    protected function setUp()
    {
        $this->command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->command->expects($this->any())
            ->method('getAggregateId')
            ->willReturn(self::AGGREGATE_ID);
    }

    public function test_dispatchCommandInvalidCommand()
    {
        $commandValidator = $this->getMockBuilder(CommandValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandValidator->expects($this->any())
            ->method('validateCommand')
            ->with($this->equalTo($this->command))
            ->willReturn(['some error']);

        $commandTester = $this->getMockBuilder(CommandTester::class)
            ->getMock();

        $commandTester
            ->method('canExecuteCommand')
            ->willReturn(true);

        /** @var CommandValidator $commandValidator */
        /** @var CommandTester $commandTester */

        $commandDispatcherWithValidator = new CommandTesterWithValidator(
            $commandTester, $commandValidator);

        $this->assertFalse($commandDispatcherWithValidator->canExecuteCommand($this->command));
   }


    /**
     *
     */
    public function test_dispatchCommandValidCommand()
    {
        $someValidatorException = new \Exception("Some validator exception");
        $someValidatorStringException = "some string exception";
        $someTesterException = new \Exception("Some tester exception");

        $commandValidator = $this->getMockBuilder(CommandValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandValidator->expects($this->any())
            ->method('validateCommand')
            ->with($this->equalTo($this->command))
            ->willReturn([$someValidatorException, $someValidatorStringException]);

        $commandTester = $this->getMockBuilder(CommandTesterWithExplanation::class)
            ->getMock();

        $commandTester
            ->method('whyCantExecuteCommand')
            ->willReturn([$someTesterException]);

        /** @var CommandValidator $commandValidator */
        /** @var CommandTesterWithExplanation $commandTester */
        $commandTesterWithValidator = new CommandTester\CommandTesterWithExplanationWithValidator(
            $commandTester, $commandValidator);

        $errors = $commandTesterWithValidator->whyCantExecuteCommand($this->command);

        $this->assertCount(3, $errors);
    }
}