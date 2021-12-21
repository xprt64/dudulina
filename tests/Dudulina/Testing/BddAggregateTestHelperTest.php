<?php


namespace tests\Dudulina\Testing;


use Dudulina\Command;
use Dudulina\Command\CommandSubscriber;
use Dudulina\Command\Exception\CommandHandlerNotFound;
use Dudulina\Command\ValueObject\CommandHandlerDescriptor;
use Dudulina\Event;
use Dudulina\Event\EventsApplier\EventsApplierOnAggregate;
use Dudulina\Testing\BddAggregateTestHelper;
use Dudulina\Testing\Exceptions\TooManyEventsFired;
use Dudulina\Testing\Exceptions\ExpectedEventNotYielded;
use Dudulina\Testing\Exceptions\NoExceptionThrown;
use Dudulina\Testing\Exceptions\WrongEventClassYielded;
use Dudulina\Testing\Exceptions\WrongExceptionClassThrown;
use Dudulina\Testing\Exceptions\WrongExceptionMessageWasThrown;


class BddAggregateTestHelperTest extends \PHPUnit\Framework\TestCase
{

    public function test()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);
        /** @var Command $command */

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        /** @var Event $event2 */
        $event2 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willReturn(new \ArrayIterator([$event2]));

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->then($event2);

        $this->assertTrue(true);
    }

    private function generateRandomEvent()
    {
        return $this->getMockBuilder(Event::class)
            ->setMockClassName('Event' . md5(random_bytes(10)))
            ->getMock();
    }

    public function test_WrongEventClassYielded()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        $event1 = $this->generateRandomEvent();

        $expectedEvent = $this->generateRandomEvent();

        $actualEvent = $this->generateRandomEvent();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willReturn(new \ArrayIterator([$actualEvent]));

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        $this->expectException(WrongEventClassYielded::class);

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->then($expectedEvent);
    }

    public function test_ExpectedEventNotYielded()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        $event1 = $this->generateRandomEvent();
        $expectedEvent2 = $this->generateRandomEvent();
        $actualEvent = $this->generateRandomEvent();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willReturn(new \ArrayIterator([$actualEvent]));

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        $this->expectException(ExpectedEventNotYielded::class);

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->then($actualEvent, $expectedEvent2);
    }

    public function test_EventNotExpected()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        $event1 = $this->generateRandomEvent();
        $actualEvent = $this->generateRandomEvent();
        $actualEvent2 = $this->generateRandomEvent();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willReturn(new \ArrayIterator([$actualEvent, $actualEvent2]));

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        $this->expectException(TooManyEventsFired::class);

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->then($actualEvent);
    }

    public function testWithException()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $expectedException = new \Exception("a test message");

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willThrowException($expectedException);

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->thenShouldFailWith(get_class($expectedException), $expectedException->getMessage());

        $this->assertTrue(true);
    }

    public function testWithExceptionWithWrongMessage()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $expectedException = new \Exception("a test message");

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willThrowException($expectedException);

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $this->expectException(WrongExceptionMessageWasThrown::class);

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->thenShouldFailWith(get_class($expectedException), "other message");
    }

    public function testWithExceptionWithWrongExceptionClass()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        /** @var \Exception $expectedException */
        $expectedException = new class extends \Exception {
        };
        /** @var \Exception $thrownException */
        $thrownException = new class extends \Exception {
        };

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willThrowException($thrownException);

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $this->expectException(WrongExceptionClassThrown::class);

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->thenShouldFailWith(get_class($expectedException));
    }

    public function testWithExceptionWithoutThrowingException()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        /** @var Event $event2 */
        $event2 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $expectedException = new \Exception("a test message");

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willReturn(new \ArrayIterator([$event2]));

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $this->expectException(NoExceptionThrown::class);

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->when($command);
        $sut->thenShouldFailWith(get_class($expectedException), "some message");
    }

    public function testWithoutACommand()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();
        $command->method('getAggregateId')
            ->willReturn(123);

        /** @var Event $event1 */
        $event1 = $this->getMockBuilder(Event::class)
            ->getMock();

        /** @var Event $event2 */
        $event2 = $this->getMockBuilder(Event::class)
            ->getMock();

        $aggregateCommandHandlerName = 'handle' . get_class($command);

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods([$aggregateCommandHandlerName, $this->getApplyEventMethodName($event1)])
            ->getMock();

        $expectedException = new \Exception("a test message");

        $aggregate->method($aggregateCommandHandlerName)
            ->with($command)
            ->willReturn(new \ArrayIterator([$event2]));

        $aggregate->method($this->getApplyEventMethodName($event1))
            ->with($event1);

        $commandSubscriber = $this->getMockBuilder(CommandSubscriber::class)
            ->getMock();

        $commandSubscriber
            ->method('getHandlerForCommand')
            ->with($command)
            ->willReturn(new CommandHandlerDescriptor(
                get_class($aggregate),
                $aggregateCommandHandlerName
            ));

        /** @var CommandSubscriber $commandSubscriber */

        $sut = new BddAggregateTestHelper(
            $commandSubscriber
        );

        $this->expectException(\Exception::class);

        $sut->onAggregate($aggregate);
        $sut->given($event1);
        $sut->thenShouldFailWith(get_class($expectedException), "some message");
    }

    private function getApplyEventMethodName($event1)
    {
        return EventsApplierOnAggregate::getMethodName($event1);
    }
}
