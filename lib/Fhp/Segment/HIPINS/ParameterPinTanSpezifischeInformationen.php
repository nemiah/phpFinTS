<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIPINS;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter PIN/TAN-spezifische Informationen
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: B.8.1
 */
class ParameterPinTanSpezifischeInformationen extends BaseDeg
{
    public ?int $minimalePinLaenge = null;
    public ?int $maximalePinLaenge = null;
    public ?int $maximaleTanLaenge = null;
    /** Max length: 30; Label for the username field in the UI. */
    public ?string $textZurBelegungDerBenutzerkennung = null;
    /** Max length: 30; */
    public ?string $textZurBelegungDerKundenId = null;
    /** @var GeschaeftsvorfallspezifischePinTanInformationen[] @Max(999) */
    public array $geschaeftsvorfallspezifischePinTanInformationen;
}
