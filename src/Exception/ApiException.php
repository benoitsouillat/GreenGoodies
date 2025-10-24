<?php

namespace App\Exception;

use Exception;


/**
 * Class ApiException
 * Custom exception class for API-related errors.
 */
class ApiException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
