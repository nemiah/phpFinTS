<?php

namespace Fhp\Segment\BSE;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\DSE\HIDXES;
use Fhp\Segment\DSE\SEPADirectDebitMinimalLeadTimeProvider;

/**
 * Segment: Terminierte SEPA-Einzellastschrift einreichen Parameter
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.6.2.1 c)
 */
class HIBSESv1 extends BaseGeschaeftsvorfallparameter implements HIDXES
{
    public ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV1 $parameter;

    public function getParameter(): SEPADirectDebitMinimalLeadTimeProvider
    {
        return $this->parameter;
    }

    public function createRequestSegment(): BaseSegment
    {
        return HKBSEv1::createEmpty();
    }
}
