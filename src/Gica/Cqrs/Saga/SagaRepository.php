<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


class SagaRepository
{

    /**
     * @var SagaPersistence
     */
    private $sagaPersistence;

    public function __construct(
        SagaPersistence $sagaPersistence
    )
    {
        $this->sagaPersistence = $sagaPersistence;
    }

    public function isEventAlreadyDispatched(string $sagaId, int $sequence, int $index): bool
    {
        $data = $this->sagaPersistence->loadData($sagaId);

        $lastSequence = $data ? $data['sequence'] : -10000;
        $lastIndex = $data ? $data['index'] : -1000;

        if ($sequence > $lastSequence || ($sequence == $lastSequence && $index > $lastIndex)) {
            return false;
        }

        return true;
    }

    public function persistLastProcessedEventBySaga(string $sagaId, int $sequence, int $index)
    {
        $this->sagaPersistence->saveData($sagaId, [
            'sequence' => $sequence,
            'index'    => $index,
        ]);
    }

    public function getLastPersistedEventSequenceAndIndex(string $sagaId)
    {
        $data = $this->sagaPersistence->loadData($sagaId);

        return $data ? [$data['sequence'], $data['index']] : [];
    }
}