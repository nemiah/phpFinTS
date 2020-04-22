<?php

namespace Fhp\Options;

/**
 * Login information for a user.
 */
class Credentials
{
    /** @var string */
    protected $benutzerkennung;
    /** @var string */
    protected $pin;

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
        if (strlen($benutzerkennung) === 0) {
            throw new \InvalidArgumentException('benutzerkennung cannot be empty');
        }
        if (strlen($pin) === 0) {
            throw new \InvalidArgumentException('pin cannot be empty');
        }

        $result = new Credentials();
        $result->benutzerkennung = $benutzerkennung;
        $result->pin = $pin;
        return $result;
    }

    /** @return string */
    public function getBenutzerkennung(): string
    {
        return $this->benutzerkennung;
    }

    /** @return string */
    public function getPin(): string
    {
        return $this->pin;
    }

    public function __debugInfo()
    {
        return null; // Prevent sensitive data from leaking into logs through var_dump() or print_r().
    }
}
