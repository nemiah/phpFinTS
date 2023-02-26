<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Gültigkeitsdatum und –uhrzeit für Challenge (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section D (letter G)
 */
class GueltigkeitsdatumUndUhrzeitFuerChallenge extends BaseDeg
{
    /** JJJJMMTT gemäß ISO 8601 */
    public string $datum;
    /** hhmmss gemäß ISO 8601, local time (no time zone support). */
    public string $uhrzeit;
}
