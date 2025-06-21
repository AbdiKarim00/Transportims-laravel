<?php

namespace App\Exceptions;

use Exception;

class DepreciationCalculationException extends Exception
{
    /**
     * Create a new DepreciationCalculationException instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "Cannot calculate depreciation: missing required vehicle data.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}