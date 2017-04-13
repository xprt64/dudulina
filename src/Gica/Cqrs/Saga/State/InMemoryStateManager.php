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

        return null;
    }

    public function hasState(string $stateClass, $stateId)
    {
        $key = $stateClass . $stateId;

        return isset($this->states[$key]);
    }

    public function updateState($stateId, callable $updater)
    {
        list($stateClass, $isOptional) = $this->getStateClass($updater);

        $oldState = $this->loadState($stateClass, $stateId);
        if (!$this->hasState($stateClass, $stateId)) {
            if (!$isOptional) {
                $oldState = new $stateClass;
            }
        }

        $newState = call_user_func($updater, $oldState);

        $key = $stateClass . $stateId;

        $this->states[$key] = $newState;
    }

    private function getStateClass(callable $update)
    {
        $reflection = new \ReflectionFunction($update);

        if ($reflection->getNumberOfParameters() <= 0) {
            throw new \Exception("Updater callback must have one type-hinted parameter");
        }

        $parameter = $reflection->getParameters()[0];

        return [$parameter->getClass()->name, $parameter->isOptional()];
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