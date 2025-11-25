<?php
/** @noinspection PhpUnused */

namespace Fhp\Protocol;

use Fhp\Model\VopConfirmationRequest;

/**
 * Thrown when an action result is read, but the action is still pending the user's confirmation of the Verification of
 * Payee result.
 */
class VopConfirmationRequiredException extends \RuntimeException
{
    private VopConfirmationRequest $vopConfirmationRequest;

    public function __construct(VopConfirmationRequest $vopConfirmationRequest)
    {
        parent::__construct('This action needs VOP confirmation before it will be executed.');
        $this->vopConfirmationRequest = $vopConfirmationRequest;
    }

    public function getVopConfirmationRequest(): VopConfirmationRequest
    {
        return $this->vopConfirmationRequest;
    }
}
