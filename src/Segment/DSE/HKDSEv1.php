<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Einreichung terminierter SEPA-Einzellastschriften (Segmentversion 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.5.4.1
 */
class HKDSEv1 extends BaseSegment
{
    /** IBAN/BIC must match <DbtrAcct> and <DbtrAgt> in the XML Below. */
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;

    /** Max length: 256 */
    public string $sepaDescriptor;

    /** XML */
    public Bin $sepaPainMessage;
}
