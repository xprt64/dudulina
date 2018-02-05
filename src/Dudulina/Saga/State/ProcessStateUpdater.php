<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Saga\State;


interface ProcessStateUpdater
{
    public function createStorage(string $namespace);
    public function updateState($stateId, callable $updater, string $namespace);
    public function clearAllStates(string $namespace);
}