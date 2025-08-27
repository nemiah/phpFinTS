<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Kreditinstitutskennung (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: B.2
 */
class Kik extends BaseDeg
{
    public const DEFAULT_COUNTRY_CODE = '280'; // Germany

    /** (ISO 3166-1; has leading zeros; Germany is 280, see also chapter E.4 */
    public ?string $laenderkennzeichen;  // Officially it's mandatory, but in practice it can be missing.
    /** Max length: 30 (Mandatory/absent depending on the country) */
    public ?string $kreditinstitutscode = null;

    /** {@inheritdoc} */
    public function validate()
    {
        parent::validate();
        if ($this->laenderkennzeichen === self::DEFAULT_COUNTRY_CODE && $this->kreditinstitutscode === null) {
            throw new \InvalidArgumentException('Kik.kreditinstitutscode is mandatory for German banks (BLZ)');
        }
    }

    public static function create(string $kreditinstitutscode): Kik
    {
        $result = new Kik();
        $result->laenderkennzeichen = static::DEFAULT_COUNTRY_CODE;
        $result->kreditinstitutscode = $kreditinstitutscode;
        return $result;
    }
}
