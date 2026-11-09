<?php
/*
 * Copyright (c) 2025 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Attributes;

/**
 * Marks the public method of an aggregate that applies an event to the aggregate's state.
 *
 * The first parameter must be typed with the event class (or a union of event classes); the method is then called with the event
 * and its metadata, whatever its name is. Without the attribute, the method must be named apply<EventShortName>
 * (see {@see \Dudulina\Event\EventsApplier\EventsApplierOnAggregate}).
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AggregateEventApplier
{
}
