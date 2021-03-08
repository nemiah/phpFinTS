<?php
/** @noinspection PhpUnused */

namespace Fhp\Protocol;

/**
 * Thrown when an action result is read, but it is not available yet because the action was not executed.
 */
class ActionIncompleteException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('This action needs to be executed for the result to become available.');
    }
}
