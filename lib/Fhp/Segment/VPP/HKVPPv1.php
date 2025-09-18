<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

class HKVPPv1 extends BaseSegment
{
    public UnterstuetztePaymentStatusReports $unterstuetztePaymentStatusReports;

    public ?Bin $pollingId = null;

    /** Only allowed if {@link ParameterNamensabgleichPruefauftrag::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;

    /** For pagination. Max length: 35 */
    public ?string $aufsetzpunkt = null;
}