<?php

namespace Fhp;

/**
 * Thrown when trying to use a feature/variant that is not implemented in this library.
 */
class UnsupportedException extends \RuntimeException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
