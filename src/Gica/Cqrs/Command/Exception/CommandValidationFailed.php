<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\Exception;


class CommandValidationFailed extends \Exception
{

    /**
     * @var array
     */
    private $errors;

    public function __construct(array $errors = [])
    {
        $this->errors = $errors;

        parent::__construct(implode("\n", $this->errors));
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}