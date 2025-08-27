<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;

/**
 * Data ELement Group: Schlüsselname (Version 3)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: D
 */
class SchluesselnameV3 extends BaseDeg
{
    /**
     * Indicates that the key is to be used for cryptographic signatures.
     */
    public const SIGNIERSCHLUESSEL = 'S';
    /**
     * Indicates that the key is to be used for cryptographic ciphers (that is, encryption).
     */
    public const CHIFFRIERSCHLUESSEL = 'V';

    public \Fhp\Segment\Common\Kik $kreditinstitutskennung;
    /** This is the username used for login. */
    public string $benutzerkennung;
    /**
     * D: Schlüssel zur Erzeugung digitaler Signaturen (DS-Schlüssel)
     * S: Signierschlüssel
     * V: Chiffrierschlüssel
     * (Version 2)
     */
    public string $schluesselart;
    public int $schluesselnummer = 0; // Dummy value for PIN/TAN.
    public int $schluesselversion = 0; // Dummy value for PIN/TAN.

    public static function create(\Fhp\Segment\Common\Kik $kik, string $benutzerkennung, string $schluesselart): SchluesselnameV3
    {
        $result = new SchluesselnameV3();
        $result->kreditinstitutskennung = $kik;
        $result->benutzerkennung = $benutzerkennung;
        $result->schluesselart = $schluesselart;
        return $result;
    }
}
