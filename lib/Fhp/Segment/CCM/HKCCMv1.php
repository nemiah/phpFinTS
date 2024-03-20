<?php

/*
 * Author: Peter Eberhard, Copyright 2022 Launix Inh. Carl-Philip Hänsch
 */

namespace Fhp\Segment\CCM;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Einreichung SEPA-Sammelüberweisung
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.1.1 a)
 */
class HKCCMv1 extends BaseSegment
{
    /** @var \Fhp\Segment\Common\Kti IBAN/BIC must match <DbtrAcct> and <DbtrAgt> in the XML Below. */
    public $kontoverbindungInternational;

    /** @var \Fhp\Segment\Common\Btg|null Required if BDP „Summenfeld benötigt“ = J */
    public $summenfeld;

    /** @var bool|null Optional only if BDP „Einzelbuchung erlaubt“ = J */
    public $einzelbuchungGewuenscht;

    /** @var string Max length: 256 */
    public $sepaDescriptor;

    /**
     * HISPAS informs which XML schemas are allowed.
     * The <ReqdExctnDt> field must be 1999-01-01.
     * @var Bin XML
     */
    public $sepaPainMessage;
}
