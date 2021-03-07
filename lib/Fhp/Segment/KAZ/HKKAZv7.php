<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Kontoumsätze anfordern/Zeitraum (Version 7)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.1.1.2
 */
class HKKAZv7 extends BaseSegment implements Paginateable
{
    /** @var \Fhp\Segment\Common\Kti */
    public $kontoverbindungInternational;
    /** @var bool Only allowed if {@link ParameterKontoumsaetzeV2::$alleKontenErlaubt} says so. */
    public $alleKonten;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $vonDatum;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $bisDatum;
    /** @var int|null Only allowed if {@link ParameterKontoumsaetzeV2::$eingabeAnzahlEintraegeErlaubt} says so. */
    public $maximaleAnzahlEintraege;
    /** @var string|null Max length: 35 */
    public $aufsetzpunkt;

    public static function create(\Fhp\Segment\Common\Kti $kti, bool $alleKonten, ?\DateTime $vonDatum, ?\DateTime $bisDatum, ?string $aufsetzpunkt = null): HKKAZv7
    {
        $result = HKKAZv7::createEmpty();
        $result->kontoverbindungInternational = $kti;
        $result->alleKonten = $alleKonten;
        $result->vonDatum = $vonDatum === null ? null : $vonDatum->format('Ymd');
        $result->bisDatum = $bisDatum === null ? null : $bisDatum->format('Ymd');
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
