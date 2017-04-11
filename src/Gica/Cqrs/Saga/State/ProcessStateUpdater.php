<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga\State;


interface ProcessStateUpdater
{
    public function updateState($stateId, callable $updater);
    public function clearAllStates();
}