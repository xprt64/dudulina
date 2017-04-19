<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


use Gica\Cqrs\Saga\SagaEventTrackerRepository\ConcurentEventProcessingException;

interface SagaEventTrackerRepository
{
    public function isEventProcessingAlreadyStarted(string $sagaId, EventOrder $eventOrder): bool;

    public function isEventProcessingAlreadyEnded(string $sagaId, EventOrder $eventOrder): bool;

    /**
     * @param string $sagaId
     * @param EventOrder $eventOrder
     * @return void
     * @throws ConcurentEventProcessingException
     */
    public function startProcessingEventBySaga(string $sagaId, EventOrder $eventOrder);

    public function endProcessingEventBySaga(string $sagaId, EventOrder $eventOrder);

    public function getLastStartedEventSequenceAndIndex(string $sagaId):?EventOrder;
}