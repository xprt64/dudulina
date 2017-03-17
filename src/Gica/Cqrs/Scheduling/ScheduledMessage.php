<?php


namespace Gica\Cqrs\Scheduling;


interface ScheduledMessage
{
    public function getFireDate():\DateTimeImmutable;
    public function getMessageId();
}