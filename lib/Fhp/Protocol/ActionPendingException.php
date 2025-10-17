<?php
/** @noinspection PhpUnused */

namespace Fhp\Protocol;

use Fhp\Model\PollingInfo;

/**
 * Thrown when an action result is read, but the action is still pending a long-running operation on the server and
 * requires polling to find out when it's completed.
 */
class ActionPendingException extends \RuntimeException
{
    private PollingInfo $pollingInfo;

    public function __construct(PollingInfo $pollingInfo)
    {
        parent::__construct('This action needs polling to await finishing a server-side operation.');
        $this->pollingInfo = $pollingInfo;
    }

    public function getPollingInfo(): PollingInfo
    {
        return $this->pollingInfo;
    }
}
