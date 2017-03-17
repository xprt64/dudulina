<?php


namespace tests\Gica\Cqrs\Scheduling;


use Gica\Cqrs\Command\CommandDispatcher;
use Gica\Cqrs\Scheduling\ScheduledCommand;
use Gica\Cqrs\Scheduling\ScheduledCommandStore;
use Gica\Cqrs\Scheduling\ScheduledCommandsDispatcher;


class ScheduledCommandsDispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $command = $this->getMockBuilder(ScheduledCommand::class)
            ->getMock();

        $commandDispacther = $this->getMockBuilder(CommandDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $commandDispacther->expects($this->once())
            ->method('dispatchCommand')
            ->with($command);

        $commandStore = $this->getMockBuilder(ScheduledCommandStore::class)
            ->getMock();

        $commandStore->expects($this->once())
            ->method('loadAndProcessScheduledCommands')
            ->willReturnCallback(function ($callback) use ($command) {
                call_user_func($callback, $command);
            });

        /** @var ScheduledCommandStore $commandStore */
        /** @var CommandDispatcher $commandDispacther */

        $sut = new ScheduledCommandsDispatcher($commandStore, $commandDispacther);

        $sut->run();
    }
}