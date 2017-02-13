<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CommandDispatcher;


interface AuthenticatedIdentityReaderService
{

    /**
     * @return mixed|null
     */
    public function getAuthenticatedIdentityId();
}