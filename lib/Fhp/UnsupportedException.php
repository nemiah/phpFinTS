<?php

namespace Fhp;

/**
 * Thrown when trying to use a feature/variant that is not implemented in this library.
 */
class UnsupportedException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
