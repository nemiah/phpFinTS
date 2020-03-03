<?php

namespace Fhp\Segment\DME;

use Fhp\DataTypes\Bin;
use Fhp\Segment\BaseSegment;

/**
 * Einreichung terminierter SEPA-Sammellastschrift (Segmentversion 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.2.2.1
 */
class HKDMEv1 extends BaseSegment
{
    /** @var \Fhp\Segment\Common\Kti IBAN/BIC must match <DbtrAcct> and <DbtrAgt> in the XML Below. */
    public $kontoverbindungInternational;

    /** @var \Fhp\Segment\Common\Btg|null Required if BDP „Summenfeld benötigt“ = J */
    public $summenfeld;

    /** @var bool|null Optional only if „Einzelbuchung erlaubt“ = J */
    public $einzelbuchungGewuenscht;

    /** @var string Max length: 256 */
    public $sepaDescriptor;

    /** @var Bin XML */
    public $sepaPainMessage;
}
