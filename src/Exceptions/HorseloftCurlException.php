<?php

namespace Horseloft\Core\Exceptions;

use Throwable;

class HorseloftCurlException extends \RuntimeException
{
    public function __construct($message = "", $code = 5003, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
