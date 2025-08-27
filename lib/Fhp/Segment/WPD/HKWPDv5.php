<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Depotaufstellung anfordern (Version 5)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.4.3.1 a)
 */
class HKWPDv5 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\KtvV3 $depot;
    public ?string $waehrungDerDepotaufstellung = null;
    public ?\Fhp\Segment\Common\Kursqualitaet $kursqualitaet = null;
    /** Only allowed if {@link ParameterDepotaufstellungV2::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;

    public static function create(\Fhp\Segment\Common\KtvV3 $ktv): HKWPDv5
    {
        $result = HKWPDv5::createEmpty();
        $result->depot = $ktv;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
