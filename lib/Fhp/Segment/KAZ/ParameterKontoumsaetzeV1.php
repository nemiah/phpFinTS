<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter Kontoumsätze (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: VII.2.1.1 c)
 */
class ParameterKontoumsaetzeV1 extends BaseDeg implements ParameterKontoumsaetze
{
    /** Positive, number of days. */
    public int $speicherzeitraum;
    public bool $eingabeAnzahlEintraegeErlaubt;

    public function getAlleKontenErlaubt(): bool
    {
        return false;
    }
}
