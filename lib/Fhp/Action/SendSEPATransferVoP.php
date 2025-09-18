<?php

namespace Fhp\Action;

use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\VPP\HIVPPSv1;
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
                throw new UnsupportedException('The bank requires VoP for this request, this is not implemented yet.');
            }
        }

        return $requestSegments;
    }
}