<?php

namespace Fhp\Response;

use Fhp\Model\SEPAStandingOrder;
use Fhp\Model\TANRequest;
	
class GetTANRequest extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HITAN';
	
    /**
     * Returns TANRequest object with process ID
     *
     * @return TANRequest
     */
    public function get()
    {
        $segment = $this->findSegment(static::SEG_ACCOUNT_INFORMATION);
		$details = $this->splitSegment($segment, false);
		#print_r($details);
		$request = new TANRequest();
		$request->setProcessID($details[3]);
		
		return $request;
    }
}
