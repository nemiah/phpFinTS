<?php

namespace Fhp\Model;

use Fhp\Syntax\Bin;

/**
 * Application code should not interact directly with this type, see {@link PollingInfo instead}.
 *
 * When we send a request to the bank that requires a Verification of Payee, this means that the bank server has to
 * contact another bank's server and compare payee names. Especially for larger requests (e.g. bulk transfers), this can
 * take some time. During this time, the server asks the client to poll regularly in order to find out when the process
 * is done. This class contains the state that the client needs to do this polling.
 */
class VopPollingInfo implements PollingInfo
{
    // Both of these are effectively opaque tokens that only the server understands. Our job is to relay them back to
    // the server when polling. And for some reason there's two of them.
    private string $aufsetzpunkt;
    private ?Bin $pollingId;

    private ?int $nextAttemptInSeconds = null;

    public function __construct(string $aufsetzpunkt, ?Bin $pollingId, ?int $nextAttemptInSeconds)
    {
        $this->aufsetzpunkt = $aufsetzpunkt;
        $this->pollingId = $pollingId;
        $this->nextAttemptInSeconds = $nextAttemptInSeconds;
    }

    public function getAufsetzpunkt(): string
    {
        return $this->aufsetzpunkt;
    }

    public function getPollingId(): ?Bin
    {
        return $this->pollingId;
    }

    public function getNextAttemptInSeconds(): ?int
    {
        return $this->nextAttemptInSeconds;
    }

    public function getInformationForUser(): string
    {
        return 'The bank is verifying payee information...';
    }
}
