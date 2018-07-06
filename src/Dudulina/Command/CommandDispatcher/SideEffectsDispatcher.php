<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */
declare(strict_types=1);
namespace Dudulina\Command\CommandDispatcher;

use Dudulina\Command\CommandDispatcher\DefaultCommandDispatcher\SideEffects;

interface SideEffectsDispatcher
{
    public function dispatchSideEffects(SideEffects $sideEffects): void;
}