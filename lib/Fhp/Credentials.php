<?php

namespace Fhp;

/**
 * Login information for a user.
 */
class Credentials
{
    /** @var string */
    public $benutzerkennung;
    /** @var string */
    public $pin;

    private function __construct()
    {
    }

    /**
     * Creates credentials for a German bank.
     * @param string $benutzerkennung This is the username used for login. Usually it's the same also used for web-based
     *     online banking. Most banks initially assign a number as a username. Some banks allow users to customize the
     *     username later on. Note that most banks equate user (Benutzer) and customer (Kunde), but some banks may
     *     distinguish this username (Benutzerkennung) from the customer ID (Kunden-ID) e.g. in HIUPD.
     * @param string $pin This is the PIN used for login. With most banks, the PIN does not have to be numerical but
     *     could contain alphabetical or even arbitrary characters.
     * @return Credentials A new Credentials instance.
     */
    public static function create(string $benutzerkennung, string $pin): Credentials
    {
        $result = new Credentials();
        $result->benutzerkennung = $benutzerkennung;
        $result->pin = $pin;
        return $result;
    }
}
