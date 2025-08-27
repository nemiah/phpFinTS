<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 7)
 * Parameters for: HKTANv7
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: B.5.2 c)
 */
class HITANSv7 extends BaseGeschaeftsvorfallparameter implements HITANS
{
    public ParameterZweiSchrittTanEinreichungV7 $parameterZweiSchrittTanEinreichung;

    public function getParameterZweiSchrittTanEinreichung(): ParameterZweiSchrittTanEinreichung
    {
        return $this->parameterZweiSchrittTanEinreichung;
    }
}
