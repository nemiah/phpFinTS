<?php

namespace Fhp\Segment\CAZ;

use Fhp\Segment\KAZ\ParameterKontoumsaetzeV2;

/**
 * Segment: Parameter Kontoumsätze/Zeitraum camt
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D (letter P under "Parameter Kontoumsätze/Zeitraum")
 */
class ParameterKontoumsaetzeCamt extends ParameterKontoumsaetzeV2
{
    /** @var UnterstuetzteCamtMessages */
    public $unterstuetzteCamtMessages;

    public function getUnterstuetzteCamtMessages(): UnterstuetzteCamtMessages
    {
        return $this->unterstuetzteCamtMessages;
    }
}
