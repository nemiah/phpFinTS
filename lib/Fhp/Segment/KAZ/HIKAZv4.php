<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum (Version 4)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: VII.2.1.1 b)
 */
class HIKAZv4 extends BaseSegment implements HIKAZ
{
    /** Uses SWIFT format MT940, version SRG 2001 */
    public Bin $gebuchteUmsaetze;
    /** Uses SWIFT format MT942, version SRG 2001 */
    public ?Bin $nichtGebuchteUmsaetze = null;

    public function getGebuchteUmsaetze(): Bin
    {
        return $this->gebuchteUmsaetze;
    }

    public function getNichtGebuchteUmsaetze(): ?Bin
    {
        return $this->nichtGebuchteUmsaetze;
    }
}
