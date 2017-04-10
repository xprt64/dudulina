<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentEventProcessingException;

interface SagaEventTrackerRepository
{
    public function isEventAlreadyDispatched(string $sagaId, int $sequence, int $index): bool;

    /**
     * @param string $sagaId
     * @param int $sequence
     * @param int $index
     * @throws ConcurentEventProcessingException
     */
    public function beginProcessingEventBySaga(string $sagaId, int $sequence, int $index);
    public function endProcessingEventBySaga(string $sagaId, int $sequence, int $index);

    public function getLastPersistedEventSequenceAndIndex(string $sagaId);
}