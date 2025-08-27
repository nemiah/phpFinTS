<?php

namespace Fhp\Segment\SPA;

/**
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: D ("Unterstützte SEPA-Datenformate")
 */
trait GetUnterstuetzteSepaDatenformateTrait
{
    /** @return string[] */
    public function getUnterstuetzteSepaDatenformate(): array
    {
        // Something like "sepade.pain.00x.001.0y.xsd" is allowed here, which is not a valid SEPA XML URN / Namespace
        return array_map(function ($sepaUrn) {
            return strtr($sepaUrn, [
                'sepade:xsd:' => 'urn:iso:std:iso:20022:tech:xsd:',
                'sepade:' => 'urn:iso:std:iso:20022:tech:xsd:',
                '.xsd' => '',
            ]);
        }, $this->unterstuetzteSepaDatenformate);
    }
}
