<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Saga\SagaRunner;

use Dudulina\Event\EventWithMetaData;
use Dudulina\Saga\SagaRunner\EventProcessingHasStalled;

class EventProcessingHasStalledTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $event = $this->getMockBuilder(EventWithMetaData::class)
            ->disableOriginalConstructor()
        ->getMock();

        $ex = new EventProcessingHasStalled($event);

        $this->assertSame($event, $ex->getEventWithMetadata());
    }
}
