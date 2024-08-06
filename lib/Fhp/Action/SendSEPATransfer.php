<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\SPA\HISPAS;
use Fhp\Syntax\Bin;
use Fhp\UnsupportedException;

/**
 * Initiates an outgoing wire transfer in SEPA format (PAIN XML).
 */
class SendSEPATransfer extends BaseAction
{
    /** @var SEPAAccount */
    private $account;
    /** @var string */
    private $painMessage;
    /** @var string */
    private $xmlSchema;

    /**
     * @param SEPAAccount $account The account from which the transfer will be sent.
     * @param string $painMessage An XML-formatted ISO 20022 message. You may want to use github.com/nemiah/phpSepaXml
     *     to create this.
     * @return SendSEPATransfer A new action for executing this the given PAIN message.
     */
    public static function create(SEPAAccount $account, string $painMessage): SendSEPATransfer
    {
        if (preg_match('/xmlns="(.*?)"/', $painMessage, $match) === false) {
            throw new \InvalidArgumentException('xmlns not found in the PAIN message');
        }
        $result = new SendSEPATransfer();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->xmlSchema = $match[1];
        return $result;
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        //ANALYSE XML FOR RECEIPTS AND PAYMENT DATE
        $xmlAsObject = simplexml_load_string($this->painMessage, "SimpleXMLElement", LIBXML_NOCDATA);
        $numberOfTransactions = $xmlAsObject->CstmrCdtTrfInitn->GrpHdr->NbOfTxs;
        $hasReqdExDates = false;
        foreach ($xmlAsObject->CstmrCdtTrfInitn?->PmtInf as $pmtInfo) {
            if (isset($pmtInfo->ReqdExctnDt) && $pmtInfo->ReqdExctnDt != '1999-01-01') {
                $hasReqdExDates = true;
                break;
            }
        }


        //NOW READ OUT, WICH SEGMENT SHOULD BE USED:
        if ($numberOfTransactions > 1 && $hasReqdExDates) {

            // Terminierte SEPA-Sammelüberweisung (Segment HKCME / Kennung HICMES)
            $segmentID = 'HICMES';
            $segment = \Fhp\Segment\CME\HKCMEv1::createEmpty();
        } elseif ($numberOfTransactions == 1 && $hasReqdExDates) {

            // Terminierte SEPA-Überweisung (Segment HKCSE / Kennung HICSES)
            $segmentID = 'HICSES';
            $segment = \Fhp\Segment\CSE\HKCSEv1::createEmpty();
        } elseif ($numberOfTransactions > 1 && !$hasReqdExDates) {

            // SEPA-Sammelüberweisungen (Segment HKCCM / Kennung HICSES)
            $segmentID = 'HICSES';
            $segment = \Fhp\Segment\CCM\HKCCMv1::createEmpty();
        } else {

            //SEPA Einzelüberweisung (Segment HKCCS / Kennung HICCSS).
            $segmentID = 'HICCSS';
            $segment = \Fhp\Segment\CCS\HKCCSv1::createEmpty();
        }

        if (!$bpd->supportsParameters($segmentID, 1)) {
            throw new UnsupportedException('The bank does not support ' . $segmentID . 'v1');
        }

        /** @var HISPAS $hispas */
        $parameters = $bpd->requireLatestSupportedParameters('HISPAS');
        $supportedSchemas = $parameters->getParameter()->getUnterstuetzteSepaDatenformate();
        if (!in_array($this->xmlSchema, $supportedSchemas)) {
            throw new UnsupportedException("The bank does not support the XML schema $this->xmlSchema, but only "
                . implode(', ', $supportedSchemas));
        }

        $segment->kontoverbindungInternational = Kti::fromAccount($this->account);
        $segment->sepaDescriptor = $this->xmlSchema;
        $segment->sepaPainMessage = new Bin($this->painMessage);
        return $segment;
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);
        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) === null && $response->findRueckmeldung(Rueckmeldungscode::AUSGEFUEHRT) === null) {
            throw new UnexpectedResponseException('Bank did not confirm SEPATransfer execution');
        }
    }
}
