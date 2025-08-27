<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA-Kontoverbindung rückmelden (Version 1)
 * Bezugssegment: HKSPA
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section C.10.1.3 b)
 */
class HISPAv1 extends BaseSegment implements HISPA
{
    /** @var \Fhp\Segment\Common\Ktz[]|null @Max(999) */
    public ?array $sepaKontoverbindung = null;

    /** @return \Fhp\Segment\Common\Ktz[] */
    public function getSepaKontoverbindung(): array
    {
        return $this->sepaKontoverbindung ?? [];
    }
}
