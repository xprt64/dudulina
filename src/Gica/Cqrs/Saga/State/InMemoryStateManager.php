<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga\State;


class InMemoryStateManager implements ProcessStateLoader, ProcessStateUpdater
{
    private $states = [];

    public function loadState(string $stateClass, $stateId)
    {
        $key = $stateClass . $stateId;

        if (isset($this->states[$key])) {
            return $this->states[$key];
        }

        return new $stateClass;
    }

    public function updateState($stateId, callable $updater)
    {
        $stateClass = $this->getStateClass($updater);

        $state = call_user_func($updater, $this->loadState($stateClass, $stateId));

        $key = $stateClass . $stateId;

        $this->states[$key] = $state;
    }

    private function getStateClass(callable $update): string
    {
        $reflection = new \ReflectionFunction($update);

        if ($reflection->getNumberOfParameters() <= 0) {
            throw new \Exception("Updater callback must have one type-hinted parameter");
        }

        return $reflection->getParameters()[0]->getClass()->name;
    }

    public function clearAllStates()
    {
        $this->states = [];
    }

    public function createStorage()
    {
        $this->states = [];
    }
}