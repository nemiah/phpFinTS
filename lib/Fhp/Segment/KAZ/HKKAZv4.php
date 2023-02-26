<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Kontoumsätze anfordern/Zeitraum (Version 4)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: VII.2.1.1 a)
 */
class HKKAZv4 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\Kto $kontoverbindungAuftraggeber;
    public ?string $kontowaehrung = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $vonDatum = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $bisDatum = null;
    /** Only allowed if {@link ParameterKontoumsaetzeV1::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;
    /** Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\Kto $kto, ?\DateTime $vonDatum, ?\DateTime $bisDatum, ?string $aufsetzpunkt = null): HKKAZv4
    {
        $result = HKKAZv4::createEmpty();
        $result->kontoverbindungAuftraggeber = $kto;
        $result->vonDatum = $vonDatum?->format('Ymd');
        $result->bisDatum = $bisDatum?->format('Ymd');
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
