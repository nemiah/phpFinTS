<?php

namespace Fhp\Model;

/**
 * Provides information that the client application should use to poll for the completion of a long-running operation on
 * the server.
 */
interface PollingInfo
{
    /**
     * @return ?int The number of seconds (measured from the time when the client received this {@link PollingInfo})
     *     after which the client is allowed to contact the server again regarding this action. If this returns null,
     *     there is no restriction.
     */
    public function getNextAttemptInSeconds(): ?int;

    /**
     * @return ?string An HTML-formatted text (either in the bank's language or in English!) that the application may
     *     display to the user to inform them (on a very high level) about why they have to wait.
     */
    public function getInformationForUser(): ?string;
}
