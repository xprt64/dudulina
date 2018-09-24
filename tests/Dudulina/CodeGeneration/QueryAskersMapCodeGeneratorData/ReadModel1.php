<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration\QueryAskersMapCodeGeneratorData;


use Dudulina\Event;
use Dudulina\ReadModel\ReadModelInterface;

class ReadModel1
{

    public function whenAnsweredWhatIsTheAnswerToOnePlusOne(WhatIsTheAnswerToOnePlusOne $event)
    {

    }
}

class WhatIsTheAnswerToOnePlusOne implements Event
{

}