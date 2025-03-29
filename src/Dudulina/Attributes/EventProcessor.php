<?php
/*
 * Copyright (c) 2021 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class EventProcessor

{
    public function __construct(
        public ?string $processGroup = null
    )
    {
    }
}