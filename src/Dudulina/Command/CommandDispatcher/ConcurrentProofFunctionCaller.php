<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Dudulina\Command\CommandDispatcher;


use Dudulina\Command\Exception\TooManyCommandExecutionRetries;
use Dudulina\EventStore\Exception\ConcurrentModificationException;

class ConcurrentProofFunctionCaller
{
    public const DEFAULT_MAXIMUM_RETRIES = 50;

    public function executeFunction($pureFunction, array $arguments = [], int $maximumSaveRetries = self::DEFAULT_MAXIMUM_RETRIES)
    {
        $retries = -1;
        do {
            try {

                /**
                 * The real function call
                 */
                return \call_user_func_array($pureFunction, $arguments);

            } catch (ConcurrentModificationException $e) {

                $retries++;
                if ($retries >= $maximumSaveRetries) {
                    break;
                }

                continue;//retry
            }

        } while (true);

        throw new TooManyCommandExecutionRetries(sprintf('TooManyCommandExecutionRetries: %d (%s)', $retries, $e->getMessage()));
    }
}