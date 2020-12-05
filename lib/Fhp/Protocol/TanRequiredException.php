<?php /** @noinspection PhpUnused */

namespace Fhp\Protocol;

use Fhp\Model\TanRequest;

/**
 * Thrown when an action result is read, but it is not available because the action requires a TAN to be completed.
 */
class TanRequiredException extends \RuntimeException
{
    /** @var TanRequest */
    private $tanRequest;

    public function __construct(TanRequest $tanRequest)
    {
        parent::__construct('This action requires a TAN to be completed.');
        $this->tanRequest = $tanRequest;
    }

    public function getTanRequest()
    {
        return $this->tanRequest;
    }
}
