<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga\State;

use Gica\Cqrs\Saga\State\InMemoryStateManager;

class InMemoryStateManagerTest extends \PHPUnit_Framework_TestCase
{

    const STATE_ID = 123;

    public function test()
    {
        $sut = new InMemoryStateManager();

        $this->assertInstanceOf(\stdClass::class, $sut->loadState(\stdClass::class, self::STATE_ID));

        $sut->updateState(self::STATE_ID, function (\stdClass $state) {
            $state->someValue = 345;

            return $state;
        });

        $this->assertSame(345, $sut->loadState(\stdClass::class, self::STATE_ID)->someValue);
    }

    public function test_invalid_updater_callback()
    {
        $sut = new InMemoryStateManager();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Updater callback must have one type-hinted parameter");

        $sut->updateState(self::STATE_ID, function () {
        });
    }

    public function test_clearAllStates()
    {
        $sut = new InMemoryStateManager();

        $sut->updateState(self::STATE_ID, function (\stdClass $state) {
            $state->someValue = 345;

            return $state;
        });

        $sut->clearAllStates();

        $this->assertObjectNotHasAttribute('someValue', $sut->loadState(\stdClass::class, self::STATE_ID));
    }
}
