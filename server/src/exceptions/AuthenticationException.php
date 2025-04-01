<?php

namespace App\Exceptions;

class AuthenticationException extends \Exception
{
    public function __construct(string $message = 'Unauthenticated', int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
