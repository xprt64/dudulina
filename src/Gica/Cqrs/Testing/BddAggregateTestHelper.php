<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Testing;


use Gica\Cqrs\Command;
use Gica\Cqrs\Command\CommandSubscriber;
use Gica\Cqrs\Event;
use Gica\Cqrs\Event\EventWithMetaData;
use Gica\Types\Guid;

abstract class BddAggregateTestHelper extends \PHPUnit_Framework_TestCase
{
    private $aggregateId;

    /** @var \Gica\Cqrs\Event\EventDispatcher */
    private $eventDispatcher;

    private $priorEvents = [];

    private $command;
    private $aggregate;
    private $aggregateHandlerMethodName;

    /** @var Event\EventsApplier\EventsApplierOnAggregate */
    private $eventsApplierOnAggregate;

    /** @var \Gica\Cqrs\Command\CommandApplier */
    private $commandApplier;

    abstract protected function getCommandSubscriber(): CommandSubscriber;

    protected function setUp()
    {
        $subscriber = new Event\EventSubscriber\ManualEventSubscriber();
        $this->eventDispatcher = new Event\EventDispatcher\EventDispatcherBySubscriber($subscriber);
        $this->eventsApplierOnAggregate = new Event\EventsApplier\EventsApplierOnAggregate();
        $this->commandApplier = new \Gica\Cqrs\Command\CommandApplier();

        $this->priorEvents = [];
        $this->command = null;
    }

    protected function onAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
        $this->aggregateId = new \Gica\Types\Guid();
    }

    protected function given(...$priorEvents)
    {
        $this->priorEvents = $this->decorateEventsWithMetadata($priorEvents);
    }

    /**
     * @param \Gica\Cqrs\Event[] $priorEvents
     * @return \Gica\Cqrs\Event\EventWithMetaData[]
     */
    private function decorateEventsWithMetadata(array $priorEvents)
    {
        return array_map(function (\Gica\Cqrs\Event $event) {
            return $this->decorateEventWithMetaData($event);
        }, $priorEvents);
    }


    protected function when($command)
    {
        $this->command = $command;
    }

    protected function then(...$expectedEvents)
    {
        $this->eventsApplierOnAggregate->applyEventsOnAggregate($this->aggregate, $this->priorEvents);

        $newEvents = $this->executeCommand($this->command);

        $this->assertTheseEvents($expectedEvents, $newEvents);
    }

    protected function executeCommand(Command $command)
    {
        $handler = $this->getCommandSubscriber()->getHandlerForCommand($command);

        $newEventsGenerator = $this->commandApplier->applyCommand($this->aggregate, $command, $handler->getMethodName());

        /** @var EventWithMetaData[] $eventsWithMetaData */
        $eventsWithMetaData = [];

        $newEvents = [];

        foreach ($newEventsGenerator as $event) {
            $eventWithMetaData = $this->decorateEventWithMetaData($event);

            $this->eventsApplierOnAggregate->applyEventsOnAggregate($this->aggregate, [$eventWithMetaData]);

            $eventsWithMetaData[] = $eventWithMetaData;
            $newEvents[] = $event;
        }

        foreach ($eventsWithMetaData as $eventWithMetaData) {
            $this->eventDispatcher->dispatchEvent($eventWithMetaData);
        }

        return $newEvents;
    }

    private function decorateEventWithMetaData(Event $event): EventWithMetaData
    {
        return new \Gica\Cqrs\Event\EventWithMetaData($event, $this->factoryMetaData());
    }

    protected function thenShouldFailWith($exceptionClass, $exceptionMessage = null)
    {
        $this->expectException($exceptionClass);
        if (null !== $exceptionMessage) {
            $this->expectExceptionMessage($exceptionMessage);
        }

        $handler = $this->getCommandSubscriber()->getHandlerForCommand($this->command);

        $this->eventsApplierOnAggregate->applyEventsOnAggregate($this->aggregate, $this->priorEvents);

        iterator_to_array($this->commandApplier->applyCommand($this->aggregate, $this->command, $handler->getMethodName()));
    }

    protected function assertTheseEvents(array $expectedEvents, array $actualEvents)
    {
        $expectedEvents = array_values($expectedEvents);
        $actualEvents = array_values($actualEvents);

        foreach ($expectedEvents as $k => $expectedEvent) {
            if (!isset($actualEvents[$k])) {
                $this->fail("Expected event #$k not fired (should have class " . get_class($expectedEvent) . ")");
                $this->assertEventsCountAreEqual($expectedEvents, $actualEvents);
            }

            $actualEvent = $actualEvents[$k];

            $this->assertEquals($this->hashEvent($expectedEvent), $this->hashEvent($actualEvent), "Wrong event #{$k} of class " . get_class($expectedEvent) . " emitted");
        }

        foreach ($actualEvents as $k => $actualEvent) {
            if (!isset($expectedEvents[$k])) {
                $this->fail("Actual event #$k fired when it should't (should have class " . get_class($actualEvent) . ")");
                $this->assertEventsCountAreEqual($expectedEvents, $actualEvents);
            }

            $expectedEvent = $expectedEvents[$k];

            $this->assertEquals($this->hashEvent($expectedEvent), $this->hashEvent($actualEvent), "Wrong event #{$k} of class " . get_class($expectedEvent) . " emitted");
        }

    }

    protected function hashEvents(array $events)
    {
        return array_map([$this, 'hashEvent'], $events);
    }

    protected function hashEvent($event)
    {
        if (null === $event) {
            $this->fail("No event emitted!");
        }

        return array_merge(['___class' => get_class($event)], (array)($event));
    }

    /**
     * @return \Gica\Cqrs\Event\EventDispatcher
     */
    public function getEventDispatcher(): \Gica\Cqrs\Event\EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    protected function assertEventsCountAreEqual(array $expectedEvents, array $actualEvents): void
    {
        $expectedCount = count($expectedEvents);
        $actualCount = count($actualEvents);
        $this->assertEquals($expectedCount, $actualCount, sprintf("%d number of events were expected but %d number of events were generated", $expectedCount, $actualCount));
    }

    private function factoryMetaData(): Event\MetaData
    {
        return new \Gica\Cqrs\Event\MetaData(
            $this->aggregateId, get_class($this->aggregate), new \DateTimeImmutable(), new Guid()
        );
    }
}