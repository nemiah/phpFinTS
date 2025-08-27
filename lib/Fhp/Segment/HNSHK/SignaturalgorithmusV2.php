<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNSHK;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Signaturalgorithmus (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: D (letter S)
 */
class SignaturalgorithmusV2 extends BaseDeg
{
    /**
     * 6: Owner Signing (OSG)
     */
    public int $verwendungDesSignaturalgorithmus = 6; // The only allowed value.
    /**
     * 1: nicht zugelassen
     * 10: RSA-Algorithmus (bei RAH)
     */
    public int $signaturalgorithmus = 10; // The only allowed value (not actually used, just as a dummy for PIN/TAN).
    /**
     * 19: RSASSA-PSS (bei RAH, RDH)
     */
    public int $operationsmodus = 19; // The only allowed value (not actually used, just as a dummy for PIN/TAN).
}
