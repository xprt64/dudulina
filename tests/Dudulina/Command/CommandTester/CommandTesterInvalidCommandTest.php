<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Command\CommandTester\CommandDispatcherInvalidCommandTest;


use Dudulina\Command;
use Dudulina\Command\CommandTester;
use Dudulina\Command\CommandTester\CommandTesterWithValidator;
use Dudulina\Command\CommandValidator;

class CommandTesterInvalidCommandTest extends \PHPUnit_Framework_TestCase
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


    public function test_dispatchCommandValidCommand()
    {
        $commandValidator = $this->getMockBuilder(CommandValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandValidator->expects($this->any())
            ->method('validateCommand')
            ->with($this->equalTo($this->command))
            ->willReturn([]);

        $commandTester = $this->getMockBuilder(CommandTester::class)
            ->getMock();

        $commandTester
            ->method('canExecuteCommand')
            ->willReturn(true);

        /** @var CommandValidator $commandValidator */
        /** @var CommandTester $commandTester */
        $commandTesterWithValidator = new CommandTesterWithValidator(
            $commandTester, $commandValidator);

        $this->assertTrue($commandTesterWithValidator->canExecuteCommand($this->command));
    }
}