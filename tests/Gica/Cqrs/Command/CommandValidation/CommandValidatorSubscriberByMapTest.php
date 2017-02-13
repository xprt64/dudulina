<?php


namespace tests\Gica\Cqrs\Command\CommandValidation;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandValidation\CommandValidatorSubscriberByMap;
use Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor;


class CommandValidatorSubscriberByMapTest extends \PHPUnit_Framework_TestCase
{

    public function testGetHandlersForCommand()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $sut = $this->getMockForAbstractClass(CommandValidatorSubscriberByMap::class);

        $sut->expects($this->once())
            ->method('getCommandHandlersDefinitions')
            ->willReturn([
                get_class($command) => [
                    ['SomeAggregate', 'someMethod'],
                    ['SomeAggregate2', 'someMethod2'],
                ],
            ]);
        /** @var CommandValidatorSubscriberByMap $sut */
        $handlers = $sut->getHandlersForCommand($command);

        $this->assertCount(2, $handlers);

        /** @var CommandHandlerDescriptor $handler1 */
        $handler1 = $handlers[0];
        $this->assertInstanceOf(CommandHandlerDescriptor::class, $handler1);
        $this->assertEquals('SomeAggregate', $handler1->getHandlerClass());
        $this->assertEquals('someMethod', $handler1->getMethodName());

        /** @var CommandHandlerDescriptor $handler2 */
        $handler2 = $handlers[1];
        $this->assertInstanceOf(CommandHandlerDescriptor::class, $handler2);
        $this->assertEquals('SomeAggregate2', $handler2->getHandlerClass());
        $this->assertEquals('someMethod2', $handler2->getMethodName());
    }

    public function testGetHandlersForCommandWithNoHandlers()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $sut = $this->getMockForAbstractClass(CommandValidatorSubscriberByMap::class);

        $sut->expects($this->once())
            ->method('getCommandHandlersDefinitions')
            ->willReturn([
            ]);
        /** @var CommandValidatorSubscriberByMap $sut */
        $handlers = $sut->getHandlersForCommand($command);

        $this->assertInternalType('array', $handlers);
        $this->assertCount(0, $handlers);
    }
}
