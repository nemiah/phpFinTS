<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: SEPA-Kontoverbindung anfordern, Parameter (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section C.10.1.4 c)
 */
class HISPASv2 extends BaseGeschaeftsvorfallparameter implements HISPAS
{
    /** @var ParameterSepaKontoverbindungAnfordernV2 */
    public $parameter;

    /** {@inheritdoc} */
    public function getParameter(): ParameterSepaKontoverbindungAnfordern
    {
        return $this->parameter;
    }
}
