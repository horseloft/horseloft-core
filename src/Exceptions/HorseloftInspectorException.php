<?php

namespace Horseloft\Core\Exceptions;

use Throwable;

class HorseloftInspectorException extends \RuntimeException
{
    public function __construct($message = "", $code = 5001, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
