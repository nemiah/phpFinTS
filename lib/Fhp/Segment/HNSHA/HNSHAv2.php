<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HNSHA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Signaturabschluss (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: B.5.2
 */
class HNSHAv2 extends BaseSegment
{
    /** @var string Max length: 14; A nonce, that matches the one in HNSHK */
    public $sicherheitskontrollreferenz;
    /** @var string|null Max length: 512; not allowed for PIN/TAN */
    public $validierungsresultat;
    /** @var BenutzerdefinierteSignaturV1|null */
    public $benutzerdefinierteSignatur;

    /**
     * @param string $sicherheitskontrollreferenz The same number that was passed to HNSHK.
     * @param BenutzerdefinierteSignaturV1 $benutzerdefinierteSignatur Contains PIN, and optionally the TAN
     * @return HNSHAv2
     */
    public static function create($sicherheitskontrollreferenz, $benutzerdefinierteSignatur)
    {
        $result = HNSHAv2::createEmpty();
        $result->sicherheitskontrollreferenz = $sicherheitskontrollreferenz;
        $result->benutzerdefinierteSignatur = $benutzerdefinierteSignatur;
        return $result;
    }
}
