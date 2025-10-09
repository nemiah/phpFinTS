<?php

namespace Fhp\Action;

use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\VPP\HIVPPSv1;
use Fhp\Segment\VPP\HIVPPv1;
use Fhp\Segment\VPP\HKVPPv1;
use Fhp\UnsupportedException;

/**
 * Initiates an outgoing wire transfer in SEPA format (PAIN XML) with VoP.
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 */
class SendSEPATransferVoP extends SendSEPATransfer
{
    protected $vopRequired = false;
    protected $vopIsPending = false;
    protected $vopNeedsConfirmation = false;

    public ?HKVPPv1 $hkvpp = null;
    public ?HIVPPv1 $hivpp = null;

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        // Do we need to ask vor the VoP Result?
        if ($this->vopIsPending) {
            $this->hkvpp->pollingId = $this->hivpp->pollingId;
            return $this->hkvpp;
        }

        $requestSegment = parent::createRequest($bpd, $upd);
        $requestSegments = [$requestSegment];

        // Check if VoP is supported by the bank

        /** @var HIVPPSv1 $hivpps */
        if ($hivpps = $bpd->getLatestSupportedParameters('HIVPPS')) {
            // Check if the request segment is in the list of VoP-supported segments
            if (in_array($requestSegment->getName(), $hivpps->parameter->vopPflichtigerZahlungsverkehrsauftrag)) {

                $this->vopRequired = true;

                // Send VoP confirmation
                if ($this->needsConfirmation() && $this->hivpp?->vopId) {
                    $hkvpp = HKVPAv1::createEmpty();
                    $hkvpp->vopId = $this->hivpp->vopId;
                    $requestSegments = [$hkvpp, $requestSegment];
                } else {
                    // Ask for VoP
                    $this->hkvpp = $hkvpp = HKVPPv1::createEmpty();

                    // For now just pretend we support all formats
                    $supportedFormats = explode(';', $hivpps->parameter->unterstuetztePaymentStatusReportDatenformate);
                    $hkvpp->unterstuetztePaymentStatusReports->paymentStatusReportDescriptor = $supportedFormats;

                    // VoP before the transfer request
                    $requestSegments = [$hkvpp, $requestSegment];
                }
            }
        }

        return $requestSegments;
    }

    public function processResponse(Message $response)
    {
        // The bank accepted the request as is.
        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) !== null || $response->findRueckmeldung(Rueckmeldungscode::AUSGEFUEHRT) !== null) {
            parent::processResponse($response);
            return;
        }

        // The Bank does not want a separate HKVPA ("VoP Ausführungsauftrag").
        if ($response->findRueckmeldung(Rueckmeldungscode::VOP_AUSFUEHRUNGSAUFTRAG_NICHT_BENOETIGT) !== null) {
            $this->vopRequired = false;
            $this->vopIsPending = false;
            $this->vopNeedsConfirmation = false;
            parent::processResponse($response);
            return;
        }

        if ($response->findRueckmeldung(Rueckmeldungscode::VOP_NAMENSABGLEICH_IST_NOCH_IN_BEARBEITUNG) !== null) {
            $this->vopIsPending = true;
            $this->vopNeedsConfirmation = false;
            return;
        }

        $this->hivpp = $response->findSegment(HIVPPv1::class);

        // The bank has discarded the request, and wants us to resend it with a HKVPA
        // This can happen even if the name matches.
        if ($response->findRueckmeldung(Rueckmeldungscode::FREIGABE_KANN_NICHT_ERTEILT_WERDEN) !== null) {
            // Result is available
            if ($this->hivpp->vopId) {
                $this->vopNeedsConfirmation = true;
            } else {
                $this->vopIsPending = true;
            }
            return;
        }

        // The user needs to check the result of the name check.
        // This can be sent by the bank even if the name matches.
        if ($response->findRueckmeldung(Rueckmeldungscode::VOP_ERGEBNIS_NAMENSABGLEICH_PRUEFEN) !== null) {

            $this->vopIsPending = false;
            $this->vopNeedsConfirmation = true;

            return;
        }
        throw new UnsupportedException('Unexpected state in VoP process');
    }

    public function needsTime()
    {
        return $this->vopIsPending;
    }

    public function needsConfirmation()
    {
        return $this->vopNeedsConfirmation;
    }

}