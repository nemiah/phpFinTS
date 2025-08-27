<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Saldenabfrage (Version 4)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI21o.pdf
 * Section: VII.2.2 a)
 */
class HKSALv4 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\Kto $kontoverbindungAuftraggeber;
    public bool $alleKonten;
    public ?string $kontowaehrung = null;
    public ?int $maximaleAnzahlEintraege = null;
    /** Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\Kto $kto, ?string $aufsetzpunkt = null): HKSALv4
    {
        $result = HKSALv4::createEmpty();
        $result->kontoverbindungAuftraggeber = $kto;
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
