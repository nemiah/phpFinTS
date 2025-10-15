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
    public const VOP_RESULT_CODE_RCVC = 'ReceivedVerificationCompleted';
    public const VOP_RESULT_CODE_RVNA = 'ReceivedVerificationCompletedNotApplicable';
    public const VOP_RESULT_CODE_RVNM = 'ReceivedVerificationCompletedNoMatch';
    public const VOP_RESULT_CODE_RVMC = 'ReceivedVerificationCompletedMatchClosely';
    public const VOP_RESULT_CODE_RVNC = 'ReceivedVerificationNotCompleted';
    public const VOP_RESULT_CODE_RVCM = 'ReceivedVerificationCompletedWithMismatches';

    public const VOP_RESULT_CODES = [
        'RCVC' => 'ReceivedVerificationCompleted',
        'RVNA' => 'ReceivedVerificationCompletedNotApplicable',
        'RVNM' => 'ReceivedVerificationCompletedNoMatch',
        'RVMC' => 'ReceivedVerificationCompletedMatchClosely',
        'RVNC' => 'ReceivedVerificationNotCompleted',
        'RVCM' => 'ReceivedVerificationCompletedWithMismatches'
    ];

    public ?Bin $vopId = null;

    public ?Tsp $vopIdGueltigBis = null;

    public ?Bin $pollingId = null;

    public ?string $paymentStatusReportDescriptor = null;

    public ?Bin $paymentStatusReport = null;

    public ?ErgebnisVopPruefungEinzeltransaktion $ergebnisVopPruefungEinzeltransaktion = null;

    public ?string $aufklaerungstextAutorisierungTrotzAbweichung = null;

    // This value is in seconds
    public ?int $wartezeitVorNaechsterAbfrage = null;

    public function getVopResultCode(): ?string
    {
        if ($this->paymentStatusReport) {
            $report = simplexml_load_string($this->paymentStatusReport->getData());
            return $report->CstmrPmtStsRpt->OrgnlGrpInfAndSts->GrpSts;
        } else {
            return $this->ergebnisVopPruefungEinzeltransaktion?->vopPruefergebnis;
        }
    }
}