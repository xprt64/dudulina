<?php


namespace Gica\Cqrs\Scheduling;


use Gica\Cqrs\Message\IdentifiedMessage;

interface ScheduledMessage extends IdentifiedMessage
{
    public function getFireDate():\DateTimeImmutable;

}