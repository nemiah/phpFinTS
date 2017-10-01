<?php

namespace Fhp\Response;

use Fhp\Model\SEPAStandingOrder;
use Fhp\Model\TANRequest;
	
/**
 * Class GetSEPAAccounts
 * @package Fhp\Response
 * @author Nena Furtmeier <support@furtmeier.it>
 */
class GetTANRequest extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HITAN';
	private $dialog;
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
	
	function __construct($rawResponse, \Fhp\Dialog\Dialog $dialog) {
		parent::__construct($rawResponse);
		
		$this->dialog = $dialog;
	}
	
	function getDialog(){
		return $this->dialog;
	}
}
