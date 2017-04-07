<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga;


interface SagaPersistence
{

    public function loadData(string $sagaId):?array;

    public function saveData(string $sagaId, ?array $data);
}