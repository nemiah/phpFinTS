<?php

namespace Fhp\Segment\HIPINS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: PIN/TAN-spezifische Informationen (Version 1)
 * Format: Geschäftsvorfallparameter.
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.8.1
 */
class HIPINSv1 extends BaseGeschaeftsvorfallparameter
{
    /** @var ParameterPinTanSpezifischeInformationen */
    public $parameter;
}
