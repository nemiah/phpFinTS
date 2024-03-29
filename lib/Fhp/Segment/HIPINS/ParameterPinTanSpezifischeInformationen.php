<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIPINS;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter PIN/TAN-spezifische Informationen
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
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
