<?php

namespace Horseloft\Core\Exceptions;

use Throwable;

class HorseloftRequestException extends \RuntimeException
{
    public function __construct($message = "", $code = 5002, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}