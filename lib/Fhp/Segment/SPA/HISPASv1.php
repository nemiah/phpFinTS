<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: SEPA-Kontoverbindung anfordern, Parameter (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section C.10.1.3 c)
 */
class HISPASv1 extends BaseGeschaeftsvorfallparameter implements HISPAS
{
    /** @var ParameterSepaKontoverbindungAnfordernV1 */
    public $parameter;

    /** {@inheritdoc} */
    public function getParameter(): ParameterSepaKontoverbindungAnfordern
    {
        return $this->parameter;
    }
}
