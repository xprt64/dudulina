<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Saga\State;


class InMemoryStateManager implements ProcessStateLoader, ProcessStateUpdater
{
    private $states = [];

    public function loadState(string $stateClass, $stateId, string $namespace = 'global_namespace')
    {
        $key = $stateClass . $stateId;

        if (isset($this->states[$namespace][$key])) {
            return $this->states[$namespace][$key];
        }

        return null;
    }

    public function hasState(string $stateClass, $stateId, string $namespace)
    {
        $key = $stateClass . $stateId;

        return isset($this->states[$namespace][$key]);
    }

    public function updateState($stateId, callable $updater, string $namespace = 'global_namespace')
    {
        list($stateClass, $isOptional) = $this->getStateClass($updater);

        $oldState = $this->loadState($stateClass, $stateId, $namespace);
        if (!$this->hasState($stateClass, $stateId, $namespace)) {
            if (!$isOptional) {
                $oldState = new $stateClass;
            }
        }

        $newState = call_user_func($updater, $oldState);

        $key = $stateClass . $stateId;

        $this->states[$namespace][$key] = $newState;
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

    public function clearAllStates(string $namespace = 'global_namespace')
    {
        $this->states[$namespace] = [];
    }

    public function createStorage(string $namespace = 'global_namespace')
    {
        $this->states[$namespace] = [];
    }
}