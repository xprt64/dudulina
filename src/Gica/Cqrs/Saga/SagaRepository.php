<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Saga;

interface SagaRepository
{
    public function loadSaga(string $sagaClass);
}