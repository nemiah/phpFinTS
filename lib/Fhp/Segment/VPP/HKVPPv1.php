<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Namensabgleich Prüfauftrag
 *
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 * Section: C.10.7.1 a)
 */
class HKVPPv1 extends BaseSegment
{
    public UnterstuetztePaymentStatusReportsV1 $unterstuetztePaymentStatusReports;

    public ?Bin $pollingId = null;

    /** Only allowed if {@link ParameterNamensabgleichPruefauftragV1::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;

    /** For pagination. Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function createEmpty(): static
    {
        $hkvpp = parent::createEmpty();
        $hkvpp->unterstuetztePaymentStatusReports = new UnterstuetztePaymentStatusReportsV1();
        return $hkvpp;
    }
}
