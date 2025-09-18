<?php

namespace Fhp\Action;

use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\VPP\HIVPPSv1;
use Fhp\Segment\VPP\HKVPPv1;
use Fhp\UnsupportedException;

class SendSEPATransferVoP extends SendSEPATransfer
{
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $requestSegment = parent::createRequest($bpd, $upd);
        $requestSegments = [$requestSegment];

        /** @var HIVPPSv1 $hivpps */
        // Check if VoP is supported by the bank
        if ($hivpps = $bpd->getLatestSupportedParameters('HIVPPS')) {
            // Check if the request segment is in the list of VoP-supported segments
            if (in_array($requestSegment->getName(), $hivpps->parameter->VoPPflichtigerZahlungsverkehrsauftrag)) {

                $hkvpp = HKVPPv1::createEmpty();

                # For now just pretend we support all formats
                $supportedFormats = explode(';', $hivpps->parameter->unterstuetztePaymentStatusReportDatenformate);
                $hkvpp->unterstuetztePaymentStatusReports->paymentStatusReportDescriptor = $supportedFormats;

                // VoP before the transfer request
                $requestSegments = [$hkvpp, $requestSegment];
            }
        }

        return $requestSegments;
    }
}