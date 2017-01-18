<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\ReadModel;


interface ReadModelInterface
{

    public function clearModel();

    public function createModel();

}