<?php


namespace tests\Dudulina\Command;


use Dudulina\Command;
use Dudulina\Command\CommandValidation\CommandValidatorSubscriber;
use Dudulina\Command\CommandValidator;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;
use Psr\Container\ContainerInterface;


class CommandValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Command */
    private $command;
    /** @var CommandValidatorSubscriber */
    private $validator;

    public function testValidateCommand()
    {
        $commandValidator = new CommandValidator(
            $this->mockCommandValidatorSubscriber(),
            $this->mockContainer()
        );
        $commandValidator->validateCommand($this->mockCommand());
    }

    private function mockValidator()
    {
        if (!$this->validator) {
            $this->validator = $this->getMockBuilder(\stdClass::class)
                ->setMethods(['validateTheCommand'])
                ->getMock();
            $this->validator->expects($this->once())
                ->method('validateTheCommand')
                ->with($this->mockCommand())
                ->willReturnCallback(function () {
                    yield "some error";
                });
        }
        /** @var CommandValidatorSubscriber $validator */
        return $this->validator;
    }

    private function mockCommand(): Command
    {
        if (!$this->command) {
            $this->command = $this->getMockBuilder(Command::class)
                ->getMock();
        }
        return $this->command;
    }

    private function mockCommandValidatorSubscriber(): CommandValidatorSubscriber
    {
        $commandValidatorSubscriber = $this->getMockBuilder(CommandValidatorSubscriber::class)
            ->getMock();
        $commandValidatorSubscriber->expects($this->once())
            ->method('getHandlersForCommand')
            ->with($this->mockCommand())
            ->willReturn([
                new CommandHandlerDescriptor(
                    \get_class($this->mockValidator()),
                    'validateTheCommand'
                ),
            ]);
        /** @var CommandValidatorSubscriber $commandValidatorSubscriber */
        return $commandValidatorSubscriber;
    }

    private function mockContainer()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with(\get_class($this->mockValidator()))
            ->willReturn($this->mockValidator());
        /** @var ContainerInterface $container */
        return $container;
    }
}
