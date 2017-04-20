<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga\State;


interface ProcessStateLoader
{
    public function loadState(string $stateClass, $stateId, string $namespace = 'global_namespace');
}