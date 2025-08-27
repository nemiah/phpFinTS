<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNSHA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Signaturabschluss (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: B.5.2
 */
class HNSHAv2 extends BaseSegment
{
    /** Max length: 14; A nonce, that matches the one in HNSHK */
    public string $sicherheitskontrollreferenz;
    /** Max length: 512; not allowed for PIN/TAN */
    public ?string $validierungsresultat = null;
    public ?BenutzerdefinierteSignaturV1 $benutzerdefinierteSignatur = null;

    /**
     * @param string $sicherheitskontrollreferenz The same number that was passed to HNSHK.
     * @param BenutzerdefinierteSignaturV1 $benutzerdefinierteSignatur Contains PIN, and optionally the TAN
     */
    public static function create(string $sicherheitskontrollreferenz, BenutzerdefinierteSignaturV1 $benutzerdefinierteSignatur): HNSHAv2
    {
        $result = HNSHAv2::createEmpty();
        $result->sicherheitskontrollreferenz = $sicherheitskontrollreferenz;
        $result->benutzerdefinierteSignatur = $benutzerdefinierteSignatur;
        return $result;
    }
}
