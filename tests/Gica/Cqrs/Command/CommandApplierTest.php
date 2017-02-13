<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/


namespace tests\Gica\Cqrs\Command;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandApplier;


class CommandApplierTest extends \PHPUnit_Framework_TestCase
{

    public function testApplyCommand()
    {
        /** @var Command $command */
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $aggregate = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['applySomeCommand'])
            ->getMock();

        $event = new \stdClass();
        $event->someProperty = 'someValue';

        $aggregate->expects($this->once())
            ->method('applySomeCommand')
            ->with($this->identicalTo($command))
            ->willReturnCallback(function () use ($event) {

                yield $event;
            });

        $sut = new CommandApplier();

        $events = iterator_to_array($sut->applyCommand($aggregate, $command, 'applySomeCommand'));

        $this->assertCount(1, $events);
        $this->assertSame($event, $events[0]);
    }
}

