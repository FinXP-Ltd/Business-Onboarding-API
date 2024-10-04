<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DocumentException extends Exception
{
    public function __construct(string $message = "", int $code = 400, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
