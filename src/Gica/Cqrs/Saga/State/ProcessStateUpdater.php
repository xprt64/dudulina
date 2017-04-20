<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga\State;


interface ProcessStateUpdater
{
    public function createStorage();
    public function updateState($stateId, callable $updater, string $namespace = 'global_namespace');
    public function clearAllStates(string $namespace = 'global_namespace');
}