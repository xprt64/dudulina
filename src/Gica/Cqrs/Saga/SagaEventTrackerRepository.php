<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentEventProcessingException;

interface SagaEventTrackerRepository
{
    public function isEventProcessingAlreadyStarted(string $sagaId, string $eventId): bool;

    public function isEventProcessingAlreadyEnded(string $sagaId, string $eventId): bool;

    /**
     * @param string $sagaId
     * @param string $eventId
     * @return void
     * @throws ConcurentEventProcessingException
     */
    public function startProcessingEventBySaga(string $sagaId, string $eventId);

    public function endProcessingEventBySaga(string $sagaId, string $eventId);

    public function clearProcessingEventBySaga(string $sagaId, string $eventId);
}