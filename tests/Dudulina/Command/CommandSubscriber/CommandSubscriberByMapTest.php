<?php


namespace tests\Dudulina\Command\CommandSubscriber;


use Dudulina\Command;
use Dudulina\Command\CommandSubscriber\CommandSubscriberByMap;
use Dudulina\Command\Exception\CommandHandlerNotFound;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;


class CommandSubscriberByMapTest extends \PHPUnit\Framework\TestCase
{

    public function testGetHandlerForCommand()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $sut = new CommandSubscriberByMap([
            \get_class($command) => [['SomeAggregate', 'someMethod']],
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

        $sut = new CommandSubscriberByMap([]);

        $this->expectException(CommandHandlerNotFound::class);

        /** @var CommandSubscriberByMap $sut */
        $sut->getHandlerForCommand($command);
    }
}
