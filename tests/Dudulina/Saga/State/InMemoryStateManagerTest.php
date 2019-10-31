<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\Saga\State;

use Dudulina\Saga\State\InMemoryStateManager;

class InMemoryStateManagerTest extends \PHPUnit_Framework_TestCase
{

    const STATE_ID = 123;

    public function test()
    {
        $sut = new InMemoryStateManager();

        $this->assertFalse($sut->hasState(\stdClass::class, self::STATE_ID, 'test_namespace', ''));
        $this->assertNull($sut->loadState(\stdClass::class, self::STATE_ID, 'test_namespace', ''));

        $sut->updateState(self::STATE_ID, function (\stdClass $state) {
            $state->someValue = 345;

            return $state;
        }, 'test_namespace');

        $this->assertSame(345, $sut->loadState(\stdClass::class, self::STATE_ID, 'test_namespace')->someValue);
    }

    public function test_defaultState()
    {
        $sut = new InMemoryStateManager();

        $namespace = '';
        $storageName = 'storage';
        $this->assertFalse($sut->hasState(\stdClass::class, self::STATE_ID, $storageName, $namespace));
        $this->assertNull($sut->loadState(\stdClass::class, self::STATE_ID, $storageName, $namespace));

        $sut->updateState(self::STATE_ID, function (\stdClass $state = null) {

            $state = new \stdClass();
            $state->someValue = 345;

            return $state;
        }, $storageName);

        $this->assertSame(345, $sut->loadState(\stdClass::class, self::STATE_ID, $storageName, $namespace)->someValue);
    }

    public function test_invalid_updater_callback()
    {
        $sut = new InMemoryStateManager();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Updater callback must have one type-hinted parameter");

        $sut->updateState(self::STATE_ID, function () {
        }, 'test_namespace');
    }

    public function test_clearAllStates()
    {
        $sut = new InMemoryStateManager();

        $sut->updateState(self::STATE_ID, function (\stdClass $state = null) {

            if (!$state) {
                $state = new \stdClass();
            }

            $state->someValue = 345;

            return $state;
        }, 'test_namespace');

        $sut->createStorage();
        $sut->clearAllStates('test_namespace');

        $this->assertFalse($sut->hasState(\stdClass::class, self::STATE_ID, 'test_namespace', ''));
    }

    public function test_moveStateToNamespace()
    {
        $sut = new InMemoryStateManager();
        $sourceNamespace = 'src';
        $destinationNamespace = 'dst';
        $storageName = 'test_namespace';
        $sut->createStorage($storageName, $sourceNamespace);
        $sut->createStorage($storageName, $destinationNamespace);

        $updater = function (\stdClass $state = null) {
            if (!$state) {
                $state = new \stdClass();
            }
            $state->someValue = 345;
            return 345;
        };
        $sut->updateState(self::STATE_ID, $updater, $storageName, $sourceNamespace);
        $this->assertTrue($sut->hasState(\stdClass::class, self::STATE_ID, $storageName, $sourceNamespace));
        $this->assertFalse($sut->hasState(\stdClass::class, self::STATE_ID, $storageName, $destinationNamespace));
        $sut->moveStorageToNamespace($sourceNamespace, $destinationNamespace);
        $this->assertFalse($sut->hasState(\stdClass::class, self::STATE_ID, $storageName, $sourceNamespace));
        $this->assertTrue($sut->hasState(\stdClass::class, self::STATE_ID, $storageName, $destinationNamespace));
    }
}
