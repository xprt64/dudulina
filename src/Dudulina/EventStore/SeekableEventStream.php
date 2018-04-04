<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventStore;


interface SeekableEventStream
{
    public function afterTimestamp($after);
}