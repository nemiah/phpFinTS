<?php

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Kontoumsätze/Zeitraum camt Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.3.1.1.1 c)
 */
class HICAZSv1 extends BaseGeschaeftsvorfallparameter
{
    /** @var ParameterKontoumsaetzeCamt */
    public $parameter;

    public function getParameter(): ParameterKontoumsaetzeCamt
    {
        return $this->parameter;
    }
}
