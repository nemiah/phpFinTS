<?php

namespace Fhp\Protocol;

/**
 * Thrown if the server responds with a syntactically valid message that does not match the protocol expectations
 * implemented in this library.
 */
class UnexpectedResponseException extends \RuntimeException
{
    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
