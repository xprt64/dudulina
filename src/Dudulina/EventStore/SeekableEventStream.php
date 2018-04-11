<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\EventStore;


interface SeekableEventStream extends EventStream
{
    public function afterSequence(string $after);
    public function beforeSequence(string $before);
    public function sort(bool $chronological);
}