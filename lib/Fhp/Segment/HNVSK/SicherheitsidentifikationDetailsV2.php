<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;

/**
 * Class SicherheitsidentifikationDetailsV2
 * Data Element Group: Sicherheitsidentifikation, Details (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D
 *
 * @package Fhp\Segment\HNVSK
 */
class SicherheitsidentifikationDetailsV2 extends BaseDeg
{
    /**
     * 1: Message Sender (MS), wenn ein Kunde etwas an sein Kreditinstitut sendet.
     * 2: Message Receiver (MR), wenn das Kreditinstitut etwas an seinen Kunden sendet
     * @var integer
     */
    public $bezeichnerFuerSicherheitspartei = 1; // Unless we receive another value that overwrites this one, we're sending.
    /** @var string|null Only allowed and mandatory for Chip-card, so this library does not support it. */
    public $cid = null;
    /** @var string|null Must be set to the $kundensystemId, or '0' during synchronization. */
    public $identifizierungDerPartei;

    /**
     * @param string $kundensystemId The Kundensystem-ID as retrieved from the bank previously, or '0' during
     *     synchronization.
     * @return SicherheitsidentifikationDetailsV2
     */
    public static function createForSender($kundensystemId)
    {
        $result = new SicherheitsidentifikationDetailsV2();
        $result->identifizierungDerPartei = $kundensystemId;
        return $result;
    }
}
