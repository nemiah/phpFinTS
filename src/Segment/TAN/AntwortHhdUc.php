<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Antwort HHD_UC (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: D (Data-Dictionary, under letter A)
 */
class AntwortHhdUc extends BaseDeg
{
    /** Max length 5 */
    public string $atc;
    /** Binary; Max length 256 */
    public string $applicationCryptogramAc;
    /** Binary; Max length 256 */
    public string $efIdData;
    /** Binary; Max length 256 */
    public string $cvr;
    /** Binary; Max length 256 */
    public string $versionsinfoDerChipTanApplication;
}
