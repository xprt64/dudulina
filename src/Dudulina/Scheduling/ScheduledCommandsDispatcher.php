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
     * @var ScheduledCommandStore
     */
    private $store;
    /**
     * @var CommandDispatcher
     */
    private $dispatcher;
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(
        ScheduledCommandStore $store,
        CommandDispatcher $dispatcher
    ) {
        $this->store = $store;
        $this->dispatcher = $dispatcher;
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

    private function logCommandException(ScheduledCommand $scheduledCommand, \Throwable $exception)
    {
        if (!$this->logger) {
            return;
        }
        $this->logger->error(
            'Scheduled command exception',
            [
                'exceptionClass' => \get_class($exception),
                'trace'          => $exception->getTrace(),
                'dueDate'        => $scheduledCommand->getFireDate()->format('c'),
                'commandClass'   => \get_class($scheduledCommand),
                'commandDump'    => print_r($scheduledCommand, true),
            ]
        );
    }
}