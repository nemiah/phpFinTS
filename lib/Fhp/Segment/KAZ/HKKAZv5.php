<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Kontoumsätze anfordern/Zeitraum (Version 5)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: VII.2.1.1 a)
 */
class HKKAZv5 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\KtvV3 $kontoverbindungAuftraggeber;
    /** Only allowed if {@link ParameterKontoumsaetzeV2::$alleKontenErlaubt} says so. */
    public bool $alleKonten;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $vonDatum = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $bisDatum = null;
    /** Only allowed if {@link ParameterKontoumsaetzeV2::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;
    /** Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\KtvV3 $ktv, bool $alleKonten, ?\DateTime $vonDatum, ?\DateTime $bisDatum, ?string $aufsetzpunkt = null): HKKAZv5
    {
        $result = HKKAZv5::createEmpty();
        $result->kontoverbindungAuftraggeber = $ktv;
        $result->alleKonten = $alleKonten;
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
