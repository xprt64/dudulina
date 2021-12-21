<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\Dudulina\Command\CommandDispatcher\CommandDispatcherInvalidCommandTest;


use Dudulina\Command;
use Dudulina\Command\CommandDispatcher;
use Dudulina\Command\CommandDispatcher\CommandDispatcherWithValidator;
use Dudulina\Command\CommandValidator;
use Dudulina\Command\Exception\CommandValidationFailed;

class CommandDispatcherInvalidCommandTest extends \PHPUnit\Framework\TestCase
{

    const AGGREGATE_ID = 123;

    /** @var Command */
    private $command;

    protected function setUp():void
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

        $commandDispatcher = $this->getMockBuilder(CommandDispatcher::class)
            ->getMock();

        $commandDispatcher
            ->method('dispatchCommand');

        /** @var CommandValidator $commandValidator */
        /** @var CommandDispatcher $commandDispatcher */

        $commandDispatcherWithValidator = new CommandDispatcherWithValidator(
            $commandDispatcher, $commandValidator);

        try {
            $commandDispatcherWithValidator->dispatchCommand($this->command);
            $this->fail("Should throw exception");

        } catch (\Throwable $exception) {
            if (!$exception instanceof CommandValidationFailed) {
                $this->fail("Wrong exception class:" . get_class($exception));
            }
            $this->assertEquals(['some error'], $exception->getErrors());
        }
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

        $commandDispatcher = $this->getMockBuilder(CommandDispatcher::class)
            ->getMock();

        $commandDispatcher
            ->method('dispatchCommand');

        /** @var CommandValidator $commandValidator */
        /** @var CommandDispatcher $commandDispatcher */
        $commandDispatcherWithValidator = new CommandDispatcherWithValidator(
            $commandDispatcher, $commandValidator);

        $commandDispatcherWithValidator->dispatchCommand($this->command);

        $this->assertTrue(true);
    }
}