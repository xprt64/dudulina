<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Message;


interface IdentifiedMessage
{
    public function getMessageId();
}