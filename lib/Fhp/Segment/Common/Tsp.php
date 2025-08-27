<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Zeitstempel (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: B.6
 */
class Tsp extends BaseDeg
{
    /** JJJJMMTT gemäß ISO 8601 */
    public string $datum;
    /** hhmmss gemäß ISO 8601, local time (no time zone support). */
    public ?string $uhrzeit = null;

    public static function create(string $datum, ?string $uhrzeit): Tsp
    {
        $result = new Tsp();
        $result->datum = $datum;
        $result->uhrzeit = $uhrzeit;
        return $result;
    }

    public function asDateTime(): \DateTime
    {
        return \DateTime::createFromFormat('Ymd His', $this->datum . ' ' . ($this->uhrzeit ?? '000000'));
    }
}
