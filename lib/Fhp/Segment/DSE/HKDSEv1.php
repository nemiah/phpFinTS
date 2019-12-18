<?php

namespace Fhp\Segment\DSE;

use Fhp\DataTypes\Bin;
use Fhp\Segment\BaseSegment;

/**
 * Einreichung terminierter SEPA-Einzellastschriften (Segmentversion 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.5.4.1
 */
class HKDSEv1 extends BaseSegment
{
    /** @var \Fhp\Segment\Common\Kti IBAN/BIC must match <DbtrAcct> and <DbtrAgt> in the XML Below. */
    public $kontoverbindungInternational;

    /** @var string Max length: 256 */
    public $sepaDescriptor;

    /** @var Bin XML */
    public $sepaPainMessage;
}
