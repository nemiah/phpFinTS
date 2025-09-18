<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Namensabgleich Prüfauftrag Parameter
 *
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 * Section: C.10.7.1 c)
 */
class HIVPPSv1 extends BaseGeschaeftsvorfallparameter
{
    public ParameterNamensabgleichPruefauftrag $parameter;
}
