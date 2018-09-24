<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration\QueryHandlersMapCodeGeneratorData;

use Dudulina\Event;

class ReadModel1
{

    /**
     * @QueryHandler
     */
    public function whenAskedWhatIsTheAnswerToOnePlusOne(WhatIsTheAnswerToOnePlusOne $event)
    {

    }
}

class WhatIsTheAnswerToOnePlusOne implements Event
{

}