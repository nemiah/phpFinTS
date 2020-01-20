<?php

namespace Fhp\Options;

/**
 * Login information for a user.
 */
class Credentials
{
    /**
     * This is the username used for login. Usually it's the same also used for web-based online banking. Most banks
     * initially assign a number as a username. Some banks allow users to customize the username later on. Note that
     * most banks equate user (Benutzer) and customer (Kunde), but some banks may distinguish this username
     * (Benutzerkennung) from the customer ID (Kunden-ID) e.g. in HIUPD.
     * @var string
     */
    public $benutzerkennung;

    /**
     * This is the PIN used for login. With most banks, the PIN does not have to be numerical but could contain
     * alphabetical or even arbitrary characters.
     * @var string
     */
    public $pin;
}
