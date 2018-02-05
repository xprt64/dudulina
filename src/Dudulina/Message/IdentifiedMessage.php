<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\Message;


interface IdentifiedMessage
{
    public function getMessageId();
}