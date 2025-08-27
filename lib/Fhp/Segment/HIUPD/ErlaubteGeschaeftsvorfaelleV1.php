<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: V.3 "Kontoinformation" > Nr. 9
 */
class ErlaubteGeschaeftsvorfaelleV1 extends BaseDeg implements ErlaubteGeschaeftsvorfaelle
{
    /** References a segment type name (Segmentkennung) */
    public string $geschaeftsvorfall;
    /** Allowed values: 0, 1, 2, 3 */
    public int $anzahlBenoetigterSignaturen;
    /** Allowed values: E, T, W, M, Z */
    public ?string $limitart = null;
    public ?\Fhp\Segment\Common\Btg $limitbetrag = null;
    /** If present, must be greater than 0 */
    public ?int $limitTage = null;

    /** {@inheritdoc} */
    public function getGeschaeftsvorfall(): string
    {
        return $this->geschaeftsvorfall;
    }
}
