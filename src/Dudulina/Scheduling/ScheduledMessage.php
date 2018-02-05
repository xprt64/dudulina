<?php


namespace Dudulina\Scheduling;


use Dudulina\Message\IdentifiedMessage;

interface ScheduledMessage extends IdentifiedMessage
{
    public function getFireDate():\DateTimeImmutable;

}