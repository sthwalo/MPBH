<?php

namespace App\Exceptions;

class BadRequestException extends \Exception
{
    public function __construct(string $message = 'Bad request', int $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
