<?php

namespace Fhp\Response;

use Fhp\Model\SEPAStandingOrder;
use Fhp\Deg;

/**
 * Class GetSEPAAccounts
 * @package Fhp\Response
 * @author Nena Furtmeier <support@furtmeier.it>
 */
class GetSEPAStandingOrders extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HICDB';

    /** @var array */
    protected $orders = array();

    /**
     * Creates SEPA standing orders array list with SEPAStandingOrder models.
     *
     * @return SEPAStandingOrder[]
     */
    public function getSEPAStandingOrdersArray()
    {
        $segments = $this->findSegments(static::SEG_ACCOUNT_INFORMATION);
		
		foreach($segments AS $segment){
			$details = $this->splitSegment($segment, false);
			
			$xml = preg_replace("/^@[0-9]+@/", "", $details[3]);
			$sxml = new \SimpleXMLElement($xml);

			$ex = explode(":", $details[5]);
			
			foreach($sxml->CstmrCdtTrfInitn->PmtInf->CdtTrfTxInf AS $target){
				
				$order = new SEPAStandingOrder();
				$order->setCreditor($target->Cdtr->Nm);
				$order->setIban($target->CdtrAcct->Id->IBAN);
				$order->setBic($target->CdtrAgt->FinInstnId->BIC);
				$order->setAmount($target->Amt->InstdAmt);
				$order->setId($details[4]);
				$order->setXML(preg_replace("/^@[0-9]+@/", "", $details[3]));
				$order->setFirstExecution($ex[0]);
				$order->setTimeUnit($ex[1]);
				$order->setInterval($ex[2]);
				$order->setExecutionDay($ex[3]);
				
				$this->orders[] = $order;
			}
			
		}
		
        return $this->orders;
    }
}
