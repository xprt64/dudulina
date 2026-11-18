<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ProgressReporting;


class TaskProgressCalculator
{
    /** @var float */
    private $startTime;

    private $step = 0;

    public function __construct(
        private int $totalSteps
    )
    {
        $this->setStartTime(microtime(true));
    }

    public function setStartTime(float $startTime)
    {
        $this->startTime = $startTime;
    }

    public function increment()
    {
        $this->step++;
    }

    public function getTotalSteps(): int
    {
        return $this->totalSteps;
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    public function calculateSpeed()
    {
        return $this->step / (microtime(true) - $this->startTime);
    }

    public function calculateEta()
    {
        return ($this->totalSteps - $this->step) / $this->calculateSpeed();
    }
}