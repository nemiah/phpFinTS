<?php

namespace Fhp\Segment\VPP;

use Fhp\Model\VopConfirmationRequestImpl;
use Fhp\Model\VopPollingInfo;
use Fhp\Model\VopVerificationResult;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\VPA\HKVPAv1;
use Fhp\UnsupportedException;

/**
 * Creates request segments and interprets response segments and Ruckmeldungscodes for anything related to VOP.
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 */
class VopHelper
{
    /**
     * @param BPD $bpd The BPD.
     * @return HKVPPv1 A segment to prompt the server to do Verification of Payee.
     */
    public static function createHKVPPForInitialRequest(BPD $bpd): HKVPPv1
    {
        // For now just pretend we support all formats.
        /** @var HIVPPSv1 $hivpps */
        $hivpps = $bpd->getLatestSupportedParameters('HIVPPS');
        $supportedFormats = explode(';', $hivpps->parameter->unterstuetztePaymentStatusReportDatenformate);

        // Note: The "Art der Lieferung Payment Status Report" (V/S) parameter only describes how the bank splits the
        // pain.002 message *if* it uses the Aufsetzpunkt mechanism, which it typically only does for large batches.
        // So we can always send the initial request and only need to look at the parameter once the bank actually
        // sends us a partial report, see checkPollingRequired().

        $hkvpp = HKVPPv1::createEmpty();
        $hkvpp->unterstuetztePaymentStatusReports->paymentStatusReportDescriptor = $supportedFormats;
        return $hkvpp;
    }

    /**
     * @param BPD $bpd The BPD.
     * @param VopPollingInfo $pollingInfo The polling info we got from the immediately preceding request.
     * @return HKVPPv1 A segment to poll the server for the completion of Verification of Payee.
     */
    public static function createHKVPPForPollingRequest(BPD $bpd, VopPollingInfo $pollingInfo): HKVPPv1
    {
        $hkvpp = static::createHKVPPForInitialRequest($bpd);
        $hkvpp->aufsetzpunkt = $pollingInfo->getAufsetzpunkt();
        $hkvpp->pollingId = $pollingInfo->getPollingId();
        return $hkvpp;
    }

    /**
     * @param Message $response The response we just received from the server.
     * @param int $hkvppSegmentNumber The number of the HKVPP segment in the request we had sent.
     * @param BPD $bpd The BPD, which tells us how the bank splits the report across multiple deliveries.
     * @return ?VopPollingInfo If the response indicates that the Verification of Payee is still ongoing, such that the
     *     client should keep polling the server to (actively) wait until the result is available, this function returns
     *     a corresponding polling info object. If no polling is required, it returns null.
     */
    public static function checkPollingRequired(
        Message $response,
        int $hkvppSegmentNumber,
        BPD $bpd,
    ): ?VopPollingInfo {
        // Note: We determine whether polling is required purely based on the presence of the primary polling token (
        // the Aufsetzpunkt is mandatory, the polling ID is optional).
        // The specification also contains the code "3093 Namensabgleich ist noch in Bearbeitung", which could also be
        // used to indicate that polling is required. But the specification does not mandate its use, and we have not
        // observed banks using it consistently, so we don't rely on it here.
        $aufsetzpunkt = $response->findRueckmeldung(Rueckmeldungscode::AUFSETZPUNKT, $hkvppSegmentNumber);
        if ($aufsetzpunkt === null) {
            return null;
        }
        /** @var HIVPPv1 $hivpp */
        $hivpp = $response->findSegment(HIVPPv1::class);
        if ($hivpp->vopId !== null) {
            // The specification says that the VOP ID is only present in the final HIVPP of an Aufsetzpunkt sequence.
            throw new UnexpectedResponseException('Got response with Aufsetzpunkt AND vopId.');
        }
        if ($hivpp->paymentStatusReport !== null) {
            // This is an intermediate delivery of the report. With "vollstaendige Lieferung" (V) each delivery
            // contains all data accumulated so far, so the final one is enough and we can discard this one. With
            // "schrittweise Lieferung" (S) the bank only sends the delta, so the client would have to stitch the
            // deliveries together, which is not implemented.
            /** @var HIVPPSv1 $hivpps */
            $hivpps = $bpd->getLatestSupportedParameters('HIVPPS');
            if ($hivpps->parameter->artDerLieferungPaymentStatusReport !== 'V') {
                throw new UnsupportedException('The stepwise transfer of VOP reports is not yet supported');
            }
        }
        return new VopPollingInfo(
            $aufsetzpunkt->rueckmeldungsparameter[0],
            $hivpp?->pollingId,
            $hivpp?->wartezeitVorNaechsterAbfrage,
        );
    }

    /**
     * @param Message $response The response we just received from the server.
     * @param int $hkvppSegmentNumber The number of the HKVPP segment in the request we had sent.
     * @return ?VopConfirmationRequestImpl If the response contains a confirmation request for the user, it is returned,
     *     otherwise null (which may imply that the action was executed without requiring confirmation).
     */
    public static function checkVopConfirmationRequired(
        Message $response,
        int $hkvppSegmentNumber,
    ): ?VopConfirmationRequestImpl {
        $codes = $response->findRueckmeldungscodesForReferenceSegment($hkvppSegmentNumber);
        if (in_array(Rueckmeldungscode::VOP_AUSFUEHRUNGSAUFTRAG_NICHT_BENOETIGT, $codes)) {
            return null;
        }
        /** @var HIVPPv1 $hivpp */
        $hivpp = $response->findSegment(HIVPPv1::class);
        if ($hivpp === null) {
            throw new UnexpectedResponseException('Missing HIVPP in response to a request with HKVPP');
        }
        if ($hivpp->vopId === null) {
            throw new UnexpectedResponseException('Missing HIVPP.vopId even though VOP should be completed.');
        }

        $verificationNotApplicableReason = null;
        $differingPayeeName = null;
        if ($hivpp->paymentStatusReport === null) {
            if ($hivpp->ergebnisVopPruefungEinzeltransaktion === null) {
                throw new UnsupportedException('Missing paymentStatusReport and ergebnisVopPruefungEinzeltransaktion');
            }
            $verificationResult = VopVerificationResult::parse(
                $hivpp->ergebnisVopPruefungEinzeltransaktion->vopPruefergebnis
            );
            $verificationNotApplicableReason = $hivpp->ergebnisVopPruefungEinzeltransaktion->grundRVNA;
            // Banks only fill this in for a close match, where they offer it as a decision aid for the user.
            $differingPayeeName = $hivpp->ergebnisVopPruefungEinzeltransaktion->abweichenderEmpfaengername;
        } else {
            $report = simplexml_load_string($hivpp->paymentStatusReport->getData());
            $verificationResult = VopVerificationResult::parse(
                $report->CstmrPmtStsRpt->OrgnlGrpInfAndSts->GrpSts ?: null
            );

            // For a single transaction, we can do better than "CompletedPartialMatch",
            // which can indicate either CompletedCloseMatch or CompletedNoMatch
            if (intval($report->CstmrPmtStsRpt->OrgnlGrpInfAndSts->OrgnlNbOfTxs ?: 0) === 1
                && $verificationResult === VopVerificationResult::CompletedPartialMatch
                && $verificationResultCode = $report->CstmrPmtStsRpt->OrgnlPmtInfAndSts->TxInfAndSts?->TxSts
            ) {
                $verificationResult = VopVerificationResult::parse($verificationResultCode);
            }
        }

        return new VopConfirmationRequestImpl(
            $hivpp->vopId,
            $hivpp->vopIdGueltigBis?->asDateTime(),
            $hivpp->aufklaerungstextAutorisierungTrotzAbweichung,
            $verificationResult,
            $verificationNotApplicableReason,
            $differingPayeeName,
        );
    }

    /**
     * @param VopConfirmationRequestImpl $vopConfirmationRequest The VOP request we're confirming.
     * @return HKVPAv1 A HKVPA segment that tells the bank the request is good to execute.
     */
    public static function createHKVPAForConfirmation(VopConfirmationRequestImpl $vopConfirmationRequest): HKVPAv1
    {
        $hkvpa = HKVPAv1::createEmpty();
        $hkvpa->vopId = $vopConfirmationRequest->getVopId();
        return $hkvpa;
    }
}
