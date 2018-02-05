<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\CommandDispatcher;


interface AuthenticatedIdentityReaderService
{

    /**
     * @return mixed|null
     */
    public function getAuthenticatedIdentityId();
}