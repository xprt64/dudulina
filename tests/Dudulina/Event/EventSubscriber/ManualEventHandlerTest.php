<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\Event\EventSubscriber;


class ManualEventHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function test_subscribeCallbackToEvent()
    {
        $handler = new ManualEventHandler();

        $callback1 = function () {
        };
        $callback2 = function () {
        };
        $handler->subscribeCallbackToEvent(someEvent::class, $callback1);
        $this->assertCount(1, $handler->getListenersForEvent(new someEvent));

        $handler->subscribeCallbackToEvent(someEvent::class, $callback2);
        $this->assertCount(2, $handler->getListenersForEvent(new someEvent));
    }

    public function test_subscribeCallbackToEvents()
    {
        $handler = new ManualEventHandler();
        $callback1 = function () {
        };
        $handler->subscribeCallbackToEvents([someEvent::class, someOtherEvent::class], $callback1);
        $this->assertCount(1, $handler->getListenersForEvent(new someEvent));
        $this->assertCount(1, $handler->getListenersForEvent(new someOtherEvent));
    }
}

class someEvent
{
}

class someOtherEvent
{

}