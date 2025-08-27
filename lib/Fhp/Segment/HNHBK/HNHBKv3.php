<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNHBK;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Nachrichtenkopf (Version 3)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: B.5.2
 */
class HNHBKv3 extends BaseSegment
{
    public const NACHRICHTENGROESSE_LENGTH = 12;

    /**
     * The length of the entire message (after encryption and compression) in bytes. While this is morally a number, the
     * specification requires padding it to 12 digits, so it is implemented as a string instead.
     */
    public string $nachrichtengroesse = '000000000000'; // Ensure this field has always length 12.
    /**
     * Version 2.0.1 : 201 (Spezifikationsstatus: obsolet)
     * Version 2.1 : 210 (Spezifikationsstatus: obsolet)
     * Version 2.2 : 220 (Spezifikationsstatus: obsolet)
     * Version 3.0 : 300
     */
    public int $hbciVersion = 300; // This library implements FinTS 3.0.
    public string $dialogId;
    /** Must be positive. */
    public int $nachrichtennummer;
    /** Never sent to server, but always present in responses. */
    public ?BezugsnachrichtV1 $bezugsnachricht = null;

    public function getNachrichtengroesse(): int
    {
        return intval($this->nachrichtengroesse);
    }

    /**
     * @param int $nachrichtengroesse Length of the entire message in bytes.
     */
    public function setNachrichtengroesse(int $nachrichtengroesse)
    {
        $this->nachrichtengroesse = str_pad($nachrichtengroesse, static::NACHRICHTENGROESSE_LENGTH, '0', STR_PAD_LEFT);
    }
}
