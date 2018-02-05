<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventProcessing;


interface InProgressProcessingEvent
{
    public function getDate():\DateTimeImmutable;

    public function getEventId();
}