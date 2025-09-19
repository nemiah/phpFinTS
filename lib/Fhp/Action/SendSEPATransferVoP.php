<?php

namespace Fhp\Action;

use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\VPP\HIVPPSv1;
use Fhp\Segment\VPP\HKVPPv1;

/**
 * Initiates an outgoing wire transfer in SEPA format (PAIN XML) with VoP.
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 */
class SendSEPATransferVoP extends SendSEPATransfer
{
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $requestSegment = parent::createRequest($bpd, $upd);
        $requestSegments = [$requestSegment];

        // Check if VoP is supported by the bank

        /** @var HIVPPSv1 $hivpps */
        if ($hivpps = $bpd->getLatestSupportedParameters('HIVPPS')) {
            // Check if the request segment is in the list of VoP-supported segments
            if (in_array($requestSegment->getName(), $hivpps->parameter->VoPPflichtigerZahlungsverkehrsauftrag)) {

                $hkvpp = HKVPPv1::createEmpty();

                // For now just pretend we support all formats
                $supportedFormats = explode(';', $hivpps->parameter->unterstuetztePaymentStatusReportDatenformate);
                $hkvpp->unterstuetztePaymentStatusReports->paymentStatusReportDescriptor = $supportedFormats;

                // VoP before the transfer request
                $requestSegments = [$hkvpp, $requestSegment];
            }
        }

        return $requestSegments;
    }
}