<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


interface SagaPersistence
{

    public function loadData(string $sagaId);

    public function saveData(string $sagaId, $data);
}