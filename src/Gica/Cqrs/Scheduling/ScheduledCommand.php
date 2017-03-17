<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Scheduling;


use Gica\Cqrs\Command;

interface ScheduledCommand extends Command, ScheduledMessage
{
}