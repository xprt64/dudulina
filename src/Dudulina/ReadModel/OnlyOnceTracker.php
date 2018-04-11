<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ReadModel;


class OnlyOnceTracker
{
    private $alreadyAppliedEventsByReadModel = [];

    public function isEventAlreadyApplied(ReadModelInterface $readModel, $eventId): bool
    {
        return isset($this->alreadyAppliedEventsByReadModel[\get_class($readModel)][(string)$eventId]);
    }

    public function markEventAsApplied(ReadModelInterface $readModel, $eventId): void
    {
        $this->alreadyAppliedEventsByReadModel[\get_class($readModel)][(string)$eventId] = true;
    }
}