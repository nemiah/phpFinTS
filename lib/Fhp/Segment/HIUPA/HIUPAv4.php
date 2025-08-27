<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Userparameter allgemein (Version 4)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 * Contains the main Userparameterdaten (UPD) data.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: E.2
 */
class HIUPAv4 extends BaseSegment
{
    public string $benutzerkennung;
    /** Note: The bank may send UPD version 0, which means these UPD are the most recent but should not be persisted. */
    public int $updVersion;
    /**
     * 0: If the bank does not explicitly declare a business transaction type (i.e. request segment type) as supported,
     *    it does not support it, so sending such a request to the bank will always lead to failure.
     * 1: Explicitly declared types are definitely supported, anything else may be reported and can be sent; the bank
     *    will check online and accept/reject accordingly.
     * @var int
     */
    public int $updVerwendung;
    /** Max length: 35 */
    public ?string $benutzername = null;
    /** Max length: 2048 */
    public ?string $erweiterungAllgemein = null;
}
