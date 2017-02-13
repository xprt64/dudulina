<?php


namespace tests\Gica\Cqrs\Command\CommandSubscriber;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandSubscriber\CommandSubscriberByMap;
use Gica\Cqrs\Command\Exception\CommandHandlerNotFound;
use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;


class CommandSubscriberByMapTest extends \PHPUnit_Framework_TestCase
{

    public function testGetHandlerForCommand()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $sut = $this->getMockForAbstractClass(CommandSubscriberByMap::class);

        $sut->expects($this->once())
            ->method('getCommandHandlersDefinitions')
            ->willReturn([
                get_class($command) => [['SomeAggregate', 'someMethod']],
            ]);
        /** @var CommandSubscriberByMap $sut */
        $handler = $sut->getHandlerForCommand($command);

        $this->assertInstanceOf(CommandHandlerDescriptor::class, $handler);
        $this->assertEquals('SomeAggregate', $handler->getHandlerClass());
        $this->assertEquals('someMethod', $handler->getMethodName());
    }

    public function testGetHandlerForCommandWithCommandHandlerNotFound()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $sut = $this->getMockForAbstractClass(CommandSubscriberByMap::class);

        $sut->expects($this->once())
            ->method('getCommandHandlersDefinitions')
            ->willReturn([
            ]);

        $this->expectException(CommandHandlerNotFound::class);

        /** @var CommandSubscriberByMap $sut */
        $sut->getHandlerForCommand($command);
    }
}
