<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Tsp;
use Fhp\Syntax\Bin;

/**
 * Segment: Namensabgleich Prüfergebnis
 *
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 * Section: C.10.7.1 b)
 */
class HIVPPv1 extends BaseSegment
{
    public ?Bin $vopId = null;

    public ?Tsp $vopIdGueltigBis = null;

    public ?Bin $pollingId = null;

    public ?string $paymentStatusReportDescriptor = null;

    public ?Bin $paymentStatusReport = null;

    public ?ErgebnisVopPruefungEinzeltransaktionV1 $ergebnisVopPruefungEinzeltransaktion = null;

    public ?string $aufklaerungstextAutorisierungTrotzAbweichung = null;

    // This value is in seconds
    public ?int $wartezeitVorNaechsterAbfrage = null;
}
