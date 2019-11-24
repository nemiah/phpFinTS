<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;

/**
 * Data ELement Group: Schlüsselname (Version 3).
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D
 */
class SchluesselnameV3 extends BaseDeg
{
    /**
     * Indicates that the key is to be used for cryptographic signatures.
     */
    const SIGNIERSCHLUESSEL = 'S';
    /**
     * Indicates that the key is to be used for cryptographic ciphers (that is, encryption).
     */
    const CHIFFRIERSCHLUESSEL = 'V';

    /** @var \Fhp\Segment\Common\Kik */
    public $kreditinstitutskennung;
    /** @var string This is the username used for login. */
    public $benutzerkennung;
    /**
     * D: Schlüssel zur Erzeugung digitaler Signaturen (DS-Schlüssel)
     * S: Signierschlüssel
     * V: Chiffrierschlüssel.
     *
     * @var string (Version 2)
     */
    public $schluesselart;
    /** @var int */
    public $schluesselnummer = 0; // Dummy value for PIN/TAN.
    /** @var int */
    public $schluesselversion = 0; // Dummy value for PIN/TAN.

    /**
     * @param \Fhp\Segment\Common\Kik $kik
     * @param string                  $benutzerkennung
     * @param string                  $schluesselart
     *
     * @return SchluesselnameV3
     */
    public static function create($kik, $benutzerkennung, $schluesselart)
    {
        $result = new SchluesselnameV3();
        $result->kreditinstitutskennung = $kik;
        $result->benutzerkennung = $benutzerkennung;
        $result->schluesselart = $schluesselart;

        return $result;
    }
}
