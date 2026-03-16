<?php

namespace Fhp\Segment\CCS;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: SEPA Einzelüberweisung (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.1 a)
 */
class HKCCSv1 extends BaseSegment
{
    /** IBAN/BIC must match <DbtrAcct> and <DbtrAgt> in the XML Below. */
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;
    /** Max length: 256 */
    public string $sepaDescriptor;
    /**
     * The PAIN message in XML format.
     * HISPAS informs which XML schemas are allowed.
     * The <ReqdExctnDt> field must be 1999-01-01.
     */
    public Bin $sepaPainMessage;
}
