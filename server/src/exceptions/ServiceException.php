<?php

namespace App\Exceptions;

class ServiceException extends \Exception
{
    public function __construct(string $message = 'Service error occurred', int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}