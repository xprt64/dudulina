<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\ProgressReporting;


interface TaskProgressReporter
{
    public function reportProgressUpdate(int $currentStep, int $steps, float $speedInItemsPerSec, float $etaInSeconds);
}