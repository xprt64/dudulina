<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Scheduling;


use Dudulina\Command\CommandDispatcher;
use Psr\Log\LoggerInterface;

class ScheduledCommandsDispatcher
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(private ScheduledCommandStore $store, private CommandDispatcher $dispatcher)
    {
    }

    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        $this->store->loadAndProcessScheduledCommands(function (ScheduledCommand $scheduledCommand) {
            try {
                $this->dispatcher->dispatchCommand($scheduledCommand);
            } catch (\Throwable $exception) {
                $this->logCommandException($scheduledCommand, $exception);
            }
        });
    }

    private function logCommandException(ScheduledCommand $scheduledCommand, \Throwable $exception): void
    {
        if (!$this->logger) {
            return;
        }
        $this->logger->error(
            'Scheduled command exception',
            [
                'exceptionClass'   => $exception::class,
                'exceptionMessage' => $exception->getMessage(),
                'trace'            => $exception->getTraceAsString(),
                'dueDate'          => $scheduledCommand->getFireDate()->format('c'),
                'commandClass'     => $scheduledCommand::class,
                'commandDump'      => print_r($scheduledCommand, true),
            ]
        );
    }
}