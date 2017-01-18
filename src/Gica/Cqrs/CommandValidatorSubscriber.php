<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs;

interface CommandValidatorSubscriber
{
    /**
     * @param \Gica\Cqrs\Command $command
     * @return \Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor[]
     * @throws \Gica\Cqrs\Exception\CommandHandlerNotFound
     */
    public function getHandlersForCommand(\Gica\Cqrs\Command $command);
}