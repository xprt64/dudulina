<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga;

use Gica\Cqrs\Saga\SagaPersistence;
use Gica\Cqrs\Saga\SagaRepository;

class SagaRepositoryTest extends \PHPUnit_Framework_TestCase
{

    const SAGA_ID = 'sagaId';

    public function test_isEventAlreadyDispatched_no_data()
    {
        $persistence = $this->getMockBuilder(SagaPersistence::class)
            ->getMock();

        $persistence->method('loadData')
            ->with(self::SAGA_ID)
            ->willReturn(null);

        $persistence
            ->expects($this->never())
            ->method('saveData');

        /** @var SagaPersistence $persistence */
        $sut = new SagaRepository($persistence);

        $this->assertFalse($sut->isEventAlreadyDispatched(self::SAGA_ID, 1, 2));
    }

    public function test_isEventAlreadyDispatched_with_data_but_not_already_parsed()
    {
        $persistence = $this->getMockBuilder(SagaPersistence::class)
            ->getMock();

        $persistence->method('loadData')
            ->with(self::SAGA_ID)
            ->willReturn([
                'sequence' => 10,
                'index'    => 19,
            ]);

        $persistence
            ->expects($this->never())
            ->method('saveData');

        /** @var SagaPersistence $persistence */
        $sut = new SagaRepository($persistence);

        $this->assertFalse($sut->isEventAlreadyDispatched(self::SAGA_ID, 10, 20));
        $this->assertFalse($sut->isEventAlreadyDispatched(self::SAGA_ID, 11, 0));
    }

    public function test_dispatchEvent_already_parsed()
    {
        $persistence = $this->getMockBuilder(SagaPersistence::class)
            ->getMock();

        $persistence->method('loadData')
            ->with(self::SAGA_ID)
            ->willReturn([
                'sequence' => 99,
                'index'    => 199,
            ]);

        /** @var SagaPersistence $persistence */
        $sut = new SagaRepository($persistence);

        $this->assertTrue($sut->isEventAlreadyDispatched(self::SAGA_ID, 10, 20));
        $this->assertTrue($sut->isEventAlreadyDispatched(self::SAGA_ID, 99, 199));
    }

    public function test_persistLastProcessedEventBySaga()
    {
        $persistence = $this->getMockBuilder(SagaPersistence::class)
            ->getMock();

        $persistence->method('saveData')
            ->with(self::SAGA_ID, [
                'sequence' => 99,
                'index'    => 199,
            ]);

        /** @var SagaPersistence $persistence */
        $sut = new SagaRepository($persistence);

        $sut->persistLastProcessedEventBySaga(self::SAGA_ID, 99, 199);
    }

    public function test_getLastPersistedEventSequenceAndIndex()
    {
        $persistence = $this->getMockBuilder(SagaPersistence::class)
            ->getMock();

        $persistence->method('loadData')
            ->with(self::SAGA_ID)
            ->willReturn([
                'sequence' => 99,
                'index'    => 199,
            ]);

        /** @var SagaPersistence $persistence */
        $sut = new SagaRepository($persistence);

        list($sequence, $index) = $sut->getLastPersistedEventSequenceAndIndex(self::SAGA_ID);

        $this->assertSame(99, $sequence);
        $this->assertSame(199, $index);
    }
}
