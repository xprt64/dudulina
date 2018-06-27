<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\Event;

/**
 * marker interface for compactable events (events that overwrite previous events of the same type in the same stream)
 */
interface Compactable
{
}