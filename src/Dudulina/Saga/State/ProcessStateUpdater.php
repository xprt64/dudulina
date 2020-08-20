<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Saga\State;


interface ProcessStateUpdater
{
    public function createStorage(string $storageName, string $namespace = '');
    public function updateState($stateId, callable $updater, string $storageName, string $namespace = '');
    public function deleteState($stateId, string $stateClass, string $storageName, string $namespace = '');
    public function updateStateIfExists($stateId, callable $updater, string $storageName, string $namespace = '');
    public function clearAllStates(string $storageName, string $namespace = '');
    public function moveEntireNamespace(string $sourceNamespace, string $destinationNamespace);
}