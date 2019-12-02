<?php

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Kontoumsätze/Zeitraum Parameter (Version 6)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.1.1.1 c)
 */
class HIKAZSv6 extends BaseGeschaeftsvorfallparameter implements HIKAZS
{
    /** @var ParameterKontoumsaetzeV2 */
    public $parameter;

    public function getParameter()
    {
        return $this->parameter;
    }
}
