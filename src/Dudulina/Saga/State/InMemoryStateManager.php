<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Saga\State;


class InMemoryStateManager implements ProcessStateLoader, ProcessStateUpdater
{
    private $states = [];

    public function loadState(string $stateClass, $stateId, string $storageName = 'default', string $namespace = '')
    {
        $key = $stateClass . $stateId;

        if (isset($this->states[$namespace][$storageName][$key])) {
            return $this->states[$namespace][$storageName][$key];
        }

        return null;
    }

    public function hasState(string $stateClass, $stateId, string $storageName, string $namespace = '')
    {
        $key = $stateClass . $stateId;

        return isset($this->states[$namespace][$storageName][$key]);
    }

    public function updateState($stateId, callable $updater, string $storageName = 'default', string $namespace = '')
    {
        list($stateClass, $isOptional) = $this->getStateClass($updater);

        $oldState = $this->loadState($stateClass, $stateId, $storageName, $namespace);
        if (!$this->hasState($stateClass, $stateId, $storageName, $namespace)) {
            if (!$isOptional) {
                $oldState = new $stateClass;
            }
        }

        $newState = call_user_func($updater, $oldState);

        $key = $stateClass . $stateId;

        $this->states[$namespace][$storageName][$key] = $newState;
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

    public function clearAllStates(string $storageName = 'default', string $namespace = '')
    {
        $this->states[$namespace][$storageName] = [];
    }

    public function createStorage(string $storageName = 'default', string $namespace = '')
    {
        $this->states[$namespace][$storageName] = [];
    }

    public function moveEntireNamespace(string $sourceNamespace, string $destinationNamespace)
    {
        $this->states[$destinationNamespace] = $this->states[$sourceNamespace];
        $this->states[$sourceNamespace] = [];
    }
}