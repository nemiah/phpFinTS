<?php /** @noinspection PhpUnused */

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
    /** @var integer|null */
    public $minimalePinLaenge;
    /** @var integer|null */
    public $maximalePinLaenge;
    /** @var integer|null */
    public $maximaleTanLaenge;
    /** @var string|null Max length: 30; Label for the username field in the UI. */
    public $textZurBelegungDerBenutzerkennung;
    /** @var string|null Max length: 30; */
    public $textZurBelegungDerKundenId;
    /** @var GeschaeftsvorfallspezifischePinTanInformationen[] @Max(999) */
    public $geschaeftsvorfallspezifischePinTanInformationen;
}
