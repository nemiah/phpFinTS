<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Sicherheitsidentifikation, Details (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: D
 */
class SicherheitsidentifikationDetailsV2 extends BaseDeg
{
    /**
     * 1: Message Sender (MS), wenn ein Kunde etwas an sein Kreditinstitut sendet.
     * 2: Message Receiver (MR), wenn das Kreditinstitut etwas an seinen Kunden sendet
     */
    public int $bezeichnerFuerSicherheitspartei = 1; // Unless we receive another value that overwrites this one, we're sending.
    /** Only allowed and mandatory for Chip-card, so this library does not support it. */
    public ?string $cid = null;
    /** Must be set to the {@link FinTs::$kundensystemId}, or '0' during synchronization. */
    public ?string $identifizierungDerPartei = null;

    /**
     * @param string $kundensystemId The Kundensystem-ID as retrieved from the bank previously, or '0' during
     *     synchronization.
     */
    public static function createForSender(string $kundensystemId): SicherheitsidentifikationDetailsV2
    {
        $result = new SicherheitsidentifikationDetailsV2();
        $result->identifizierungDerPartei = $kundensystemId;
        return $result;
    }
}
