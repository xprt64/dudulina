<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\EventProcessing;


interface InProgressProcessingEvent
{
    public function getDate():\DateTimeImmutable;

    public function getEventId();
}