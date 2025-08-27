<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter SEPA-Kontoverbindung anfordern (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: D (letter P)
 */
class ParameterSepaKontoverbindungAnfordernV2 extends BaseDeg implements ParameterSepaKontoverbindungAnfordern
{
    use GetUnterstuetzteSepaDatenformateTrait;

    public bool $einzelkontenabrufErlaubt;
    public bool $nationaleKontoverbindungErlaubt;
    public bool $strukturierterVerwendungszweckErlaubt;
    public bool $eingabeAnzahlEintraegeErlaubt;
    /** @var string[] @Max(99) Max length each: 256 */
    public array $unterstuetzteSepaDatenformate;
}
