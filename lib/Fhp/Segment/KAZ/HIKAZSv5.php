<?php

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseGeschaeftsvorfallparameterOld;

/**
 * Segment: Kontoumsätze/Zeitraum Parameter (Version 5)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: VII.2.1.1 c)
 */
class HIKAZSv5 extends BaseGeschaeftsvorfallparameterOld implements HIKAZS
{
    public ParameterKontoumsaetzeV2 $parameter;

    public function getParameter(): ParameterKontoumsaetze
    {
        return $this->parameter;
    }
}
