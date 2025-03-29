<?php
/*
 * Copyright (c) 2025 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class EventHandler

{
    public function __construct(
        public ?string $processGroup = null
    )
    {
    }
}