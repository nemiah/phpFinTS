<?php

namespace Fhp\Action;

use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\VPP\HIVPPSv1;
use Fhp\Segment\VPP\HIVPPv1;
use Fhp\Segment\VPP\HKVPAv1;
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

    protected $vopConfirmed = false;

    /**
     * If set, the last response from the server regarding this action indicated that there are more results to be
     * fetched using this pagination token. This is called "Aufsetzpunkt" in the specification.
     * Pagination is used in VoP to poll for the result of the name check.
     */
    protected ?string $paginationToken = null;

    public ?HKVPPv1 $hkvpp = null;
    public ?HIVPPv1 $hivpp = null;

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        // Do we need to ask for the VoP result?
        if ($this->vopIsPending) {
            $this->hkvpp->pollingId = $this->hivpp->pollingId;
            $this->hkvpp->aufsetzpunkt = $this->paginationToken;
            return $this->hkvpp;
        }

        $requestSegment = parent::createRequest($bpd, $upd);
        $requestSegments = [$requestSegment];

        if ($this->vopNeedsConfirmation && $this->vopConfirmed) {

            $hkvpa = HKVPAv1::createEmpty();
            $hkvpa->vopId = $this->hivpp->vopId;
            return [$hkvpa, $requestSegment];
        }

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
        $this->vopIsPending = false;
        $this->hivpp = $response->findSegment(HIVPPv1::class);

        // The Bank does not want a separate HKVPA ("VoP Ausführungsauftrag").
        if ($response->findRueckmeldung(Rueckmeldungscode::VOP_AUSFUEHRUNGSAUFTRAG_NICHT_BENOETIGT) !== null) {
            $this->vopRequired = false;
            $this->vopNeedsConfirmation = false;
            parent::processResponse($response);
            return;
        }

        if ($response->findRueckmeldung(Rueckmeldungscode::VOP_NAMENSABGLEICH_IST_NOCH_IN_BEARBEITUNG) !== null) {
            $this->vopIsPending = true;
            $this->vopNeedsConfirmation = false;
            return;
        }

        if (($pagination = $response->findRueckmeldung(Rueckmeldungscode::PAGINATION)) !== null) {
            $this->paginationToken = $pagination->rueckmeldungsparameter[0];
        }

        if (
            $response->findRueckmeldung(Rueckmeldungscode::VOP_KEINE_NAMENSABWEICHUNG) !== null
            // The bank has discarded the request, and wants us to resend it with a HKVPA
            // This can happen even if the name matches.
            || $response->findRueckmeldung(Rueckmeldungscode::FREIGABE_KANN_NICHT_ERTEILT_WERDEN) !== null
            // The user needs to check the result of the name check.
            // This can be sent by the bank even if the name matches.
            || $response->findRueckmeldung(Rueckmeldungscode::VOP_ERGEBNIS_NAMENSABGLEICH_PRUEFEN) !== null
        ) {
            $this->vopNeedsConfirmation = true;
            // Is the result already available?
            if (!$this->hivpp->vopId) {
                $this->vopIsPending = true;
            }
            return;
        }

        // The bank accepted the request as is.
        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) !== null || $response->findRueckmeldung(Rueckmeldungscode::AUSGEFUEHRT) !== null) {
            $this->vopRequired = false;
            parent::processResponse($response);
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

    public function setConfirmed()
    {
        $this->vopConfirmed = true;
    }
}