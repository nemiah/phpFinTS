<?php

namespace Fhp\Segment\CME;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: SEPA Einzelüberweisung (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.1 a)
 */
class HKCMEv1 extends BaseSegment
{
    /** IBAN/BIC must match <DbtrAcct> and <DbtrAgt> in the XML Below. */
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;

    /** Required if BDP „Summenfeld benötigt“ = J */
    public ?\Fhp\Segment\Common\Btg $summenfeld = null;

    /** Optional only if „Einzelbuchung erlaubt“ = J */
    public ?bool $einzelbuchungGewuenscht = null;

    /** Max length: 256 */
    public string $sepaDescriptor;

    /** @var Bin XML */
    public Bin $sepaPainMessage;
}
