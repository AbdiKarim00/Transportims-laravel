<?php

namespace App\Exceptions;

use Exception;

class ExportException extends Exception
{
    /**
     * Create a new ExportException instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "An export error occurred.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}