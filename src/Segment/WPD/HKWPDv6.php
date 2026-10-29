<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Depotaufstellung anfordern (Version 6)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.4.3.1a
 */
class HKWPDv6 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\KtvV3 $depot;
    public ?string $waehrungDerDepotaufstellung = null;
    public ?\Fhp\Segment\Common\Kursqualitaet $kursqualitaet = null;
    /** Only allowed if {@link ParameterDepotaufstellungV2::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\KtvV3 $ktv): HKWPDv6
    {
        $result = HKWPDv6::createEmpty();
        $result->depot = $ktv;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
