<?php


namespace tests\Dudulina\Scheduling;


use Dudulina\Command\CommandDispatcher;
use Dudulina\Scheduling\ScheduledCommand;
use Dudulina\Scheduling\ScheduledCommandStore;
use Dudulina\Scheduling\ScheduledCommandsDispatcher;
use Psr\Log\LoggerInterface;


class ScheduledCommandsDispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $command = $this->getMockBuilder(ScheduledCommand::class)
            ->getMock();

        $commandDispacther = $this->getMockBuilder(CommandDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatchCommand', 'canExecuteCommand'])
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

    public function test_withException_withLogger()
    {
        $command = $this->getMockBuilder(ScheduledCommand::class)
            ->getMock();

        $commandDispacther = $this->getMockBuilder(CommandDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatchCommand', 'canExecuteCommand'])
            ->getMock();

        $commandDispacther->expects($this->once())
            ->method('dispatchCommand')
            ->with($command)
            ->willThrowException(new \Exception("test"));

        $commandStore = $this->getMockBuilder(ScheduledCommandStore::class)
            ->getMock();

        $commandStore->expects($this->once())
            ->method('loadAndProcessScheduledCommands')
            ->willReturnCallback(function ($callback) use ($command) {
                call_user_func($callback, $command);
            });

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects($this->once())
            ->method('error');

        /** @var ScheduledCommandStore $commandStore */
        /** @var CommandDispatcher $commandDispacther */
        /** @var LoggerInterface $logger */

        $sut = new ScheduledCommandsDispatcher($commandStore, $commandDispacther);

        $sut->setLogger($logger);

        $sut->run();
    }

    public function test_withException_without()
    {
        $command = $this->getMockBuilder(ScheduledCommand::class)
            ->getMock();

        $commandDispacther = $this->getMockBuilder(CommandDispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatchCommand', 'canExecuteCommand'])
            ->getMock();

        $commandDispacther->expects($this->once())
            ->method('dispatchCommand')
            ->with($command)
            ->willThrowException(new \Exception("test"));

        $commandStore = $this->getMockBuilder(ScheduledCommandStore::class)
            ->getMock();

        $commandStore->expects($this->once())
            ->method('loadAndProcessScheduledCommands')
            ->willReturnCallback(function ($callback) use ($command) {
                call_user_func($callback, $command);
            });

        /** @var ScheduledCommandStore $commandStore */
        /** @var CommandDispatcher $commandDispacther */
        /** @var LoggerInterface $logger */

        $sut = new ScheduledCommandsDispatcher($commandStore, $commandDispacther);

        $sut->run();
    }
}