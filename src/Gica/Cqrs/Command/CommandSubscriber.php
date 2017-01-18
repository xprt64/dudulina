<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command;

interface CommandSubscriber
{
    /**
     * @param \Gica\Cqrs\Command $command
     * @return \Gica\Cqrs\Command\ValueObject\CommandHandlerDescriptor
     * @throws \Gica\Cqrs\Exception\CommandHandlerNotFound
     */
    public function getHandlerForCommand(\Gica\Cqrs\Command $command);
}